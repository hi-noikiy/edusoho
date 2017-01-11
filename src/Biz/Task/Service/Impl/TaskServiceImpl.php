<?php

namespace Biz\Task\Service\Impl;

use Biz\BaseService;
use Biz\Task\Dao\TaskDao;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Task\Strategy\StrategyContext;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Activity\Service\ActivityService;

class TaskServiceImpl extends BaseService implements TaskService
{
    public function getTask($id)
    {
        return $this->getTaskDao()->get($id);
    }

    public function createTask($fields)
    {
        $this->beginTransaction();
        try {
            $strategy = $this->createCourseStrategy($fields['fromCourseId']);

            $task = $strategy->createTask($fields);
            $this->dispatchEvent("course.task.create", new Event($task));
            $this->commit();
            return $task;
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function updateTask($id, $fields)
    {
        $this->beginTransaction();
        try {
            $task     = $this->getTask($id);
            $strategy = $this->createCourseStrategy($task['courseId']);
            $task     = $strategy->updateTask($id, $fields);
            $this->commit();
            return $task;
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function publishTask($id)
    {
        $task     = $this->getTask($id);
        $strategy = $this->createCourseStrategy($task['courseId']);

        $task = $strategy->publishTask($task);
        return $task;
    }

    public function unpublishTask($id)
    {
        $task     = $this->getTask($id);
        $strategy = $this->createCourseStrategy($task['courseId']);

        $task = $strategy->unpublishTask($task);
        return $task;
    }

    public function updateSeq($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'seq',
            'categoryId',
            'number'
        ));
        return $this->getTaskDao()->update($id, $fields);
    }

    public function updateTasks($ids, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('isFree'));

        foreach ($ids as $id) {
            $this->getTaskDao()->update($id, $fields);
        }
        return true;
    }

    public function deleteTask($id)
    {
        $task = $this->getTask($id);

        if (!$this->getCourseService()->tryManageCourse($task['courseId'])) {
            throw $this->createAccessDeniedException('无权删除任务');
        }
        $result = $this->createCourseStrategy($task['courseId'])->deleteTask($task);
        $this->biz['dispatcher']->dispatch("course.task.delete", new Event($task));
        return $result;
    }

    public function findTasksByCourseId($courseId)
    {
        return $this->getTaskDao()->findByCourseId($courseId);
    }

    public function findTasksByCourseIds($courseIds)
    {
        return $this->getTaskDao()->findByCourseIds($courseIds);
    }

    public function countTasksByCourseId($courseId)
    {
        return $this->getTaskDao()->count(array('courseId' => $courseId));
    }

    public function findTasksByIds(array $ids)
    {
        return $this->getTaskDao()->findByIds($ids);
    }

    public function findTasksFetchActivityByCourseId($courseId)
    {
        $tasks       = $this->findTasksByCourseId($courseId);
        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities  = $this->getActivityService()->findActivities($activityIds);
        $activities  = ArrayToolkit::index($activities, 'id');

        array_walk($tasks, function (&$task) use ($activities) {
            $activity         = $activities[$task['activityId']];
            $task['activity'] = $activity;
        });

        return $tasks;
    }

    public function findTasksFetchActivityAndResultByCourseId($courseId)
    {
        $tasks = $this->findTasksFetchActivityByCourseId($courseId);
        if (empty($tasks)) {
            return array();
        }

        $taskResults = $this->getTaskResultService()->findUserTaskResultsByCourseId($courseId);
        $taskResults = ArrayToolkit::index($taskResults, 'courseTaskId');

        foreach ($tasks as &$task) {
            foreach ($taskResults as $key => $result) {
                if ($key != $task['id']) {
                    continue;
                }
                $task['result'] = $result;
            }
            $task = $this->setTaskLockStatus($tasks, $task);
        }
        return $tasks;
    }


    protected function getPreTask($tasks, $currentTask)
    {
        return array_filter(array_reverse($tasks), function ($task) use ($currentTask) {
            return $currentTask['seq'] > $task['seq'];
        });
    }

    /**
     * 给定一个任务 ，判断前置解锁条件是完成
     * @param $preTasks
     * @return bool
     */
    public function isPreTasksIsFinished($preTasks)
    {
        $continue     = true;
        $canLearnTask = false;
        foreach (array_values($preTasks) as $key => $preTask) {
            if (empty($continue)) {
                break;
            }
            if ($preTask['status'] != 'published') {
                continue;
            }
            if ($preTask['isOptional']) {
                $canLearnTask = true;
            } else if ($preTask['type'] == 'live') {
                $live = $this->getActivityService()->getActivity($preTask['activityId'], true);
                if (time() > $live['endTime']) {
                    $canLearnTask = true;
                } else {
                    $isTaskLearned = $this->isTaskLearned($preTask['id']);
                    if ($isTaskLearned) {
                        $canLearnTask = true;
                    } else {
                        $canLearnTask = false;
                        $continue     = false;
                    }
                }
            } else if ($preTask['type'] == 'testpaper' and $preTask['startTime']) {
                $testPaper = $this->getActivityService()->getActivity($preTask['activityId']);
                if (time() > $preTask['startTime'] + $testPaper['ext']['limitedTime'] * 60) {
                    $canLearnTask = true;
                } else {
                    $isTaskLearned = $this->isTaskLearned($preTask['id']);
                    if ($isTaskLearned) {
                        $canLearnTask = true;
                    } else {
                        $canLearnTask = false;
                        $continue     = false;
                    }
                }
            } else {
                $isTaskLearned = $this->isTaskLearned($preTask['id']);
                if ($isTaskLearned) {
                    $canLearnTask = true;
                } else {
                    $canLearnTask = false;
                }
                $continue = false;
            }

            if ((count($preTasks) - 1) == $key) {
                break;
            }
        }
        return $canLearnTask;
    }

    public function findUserTeachCoursesTasksByCourseSetId($userId, $courseSetId)
    {
        $conditions     = array(
            'userId' => $userId
        );
        $myTeachCourses = $this->getCourseService()->findUserTeachCourses($conditions, 0, PHP_INT_MAX, true);

        $conditions = array(
            'courseIds'   => ArrayToolkit::column($myTeachCourses, 'courseId'),
            'courseSetId' => $courseSetId
        );
        $courses    = $this->getCourseService()->searchCourses($conditions, array('createdTime' => 'DESC'), 0, PHP_INT_MAX);

        return $this->findTasksByCourseIds(ArrayToolkit::column($courses, 'id'));
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        return $this->getTaskDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function count($conditions)
    {
        return $this->getTaskDao()->count($conditions);
    }

    public function startTask($taskId)
    {
        $task = $this->getTask($taskId);

        $user = $this->getCurrentUser();

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);

        if (!empty($taskResult)) {
            return;
        }

        $taskResult = array(
            'activityId'   => $task['activityId'],
            'courseId'     => $task['courseId'],
            'courseTaskId' => $task['id'],
            'userId'       => $user['id']
        );

        $this->getTaskResultService()->createTaskResult($taskResult);
    }

    public function doTask($taskId, $time = TaskService::LEARN_TIME_STEP)
    {
        $task = $this->tryTakeTask($taskId);

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);

        if (empty($taskResult)) {
            throw $this->createAccessDeniedException("task #{taskId} can not do. ");
        }

        $this->getTaskResultService()->waveLearnTime($taskResult['id'], $time);
    }

    public function finishTask($taskId)
    {
        $task = $this->tryTakeTask($taskId);

        if (!$this->isFinished($taskId)) {
            throw $this->createAccessDeniedException("can not finish task #{$taskId}.");
        }

        $this->dispatchEvent('course.task.finish', new Event($task, array('user' => $this->getCurrentUser())));

        return $this->finishTaskResult($taskId);
    }

    public function finishTaskResult($taskId)
    {
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

        if (empty($taskResult)) {
            throw $this->createAccessDeniedException('task access denied. ');
        }

        if ($taskResult['status'] === 'finish') {
            return $taskResult;
        }

        $update['updatedTime']  = time();
        $update['status']       = 'finish';
        $update['finishedTime'] = time();
        $taskResult             = $this->getTaskResultService()->updateTaskResult($taskResult['id'], $update);
        return $taskResult;
    }

    public function findFreeTasksByCourseId($courseId)
    {
        $tasks = $this->getTaskDao()->findByCourseIdAndIsFree($courseId, $isFree = true);
        $tasks = ArrayToolkit::index($tasks, 'id');
        return $tasks;
    }


    public function isFinished($taskId)
    {
        $task = $this->getTask($taskId);
        return $this->getActivityService()->isFinished($task['activityId']);
    }

    public function tryTakeTask($taskId)
    {
        if (!$this->canLearnTask($taskId)) {
            throw $this->createAccessDeniedException("the Task is Locked");
        }
        $task = $this->getTask($taskId);

        if (empty($task)) {
            throw $this->createNotFoundException("task does not exist");
        }
        return $task;
    }

    public function getNextTask($taskId)
    {
        $task = $this->getTask($taskId);

        //取得下一个发布的课时
        $conditions = array(
            'courseId' => $task['courseId'],
            'status'   => 'published',
            'seq_GT'   => $task['seq']
        );
        $nextTasks  = $this->getTaskDao()->search($conditions, array('seq' => 'ASC'), 0, 1);
        if (empty($nextTasks)) {
            return array();
        }
        $nextTask = array_shift($nextTasks);

        //判断下一个课时是否课时学习
        if (!$this->canLearnTask($nextTask['id'])) {
            return array();
        }
        return $nextTask;
    }

    public function canLearnTask($taskId)
    {
        $task = $this->getTask($taskId);
        list($course) = $this->getCourseService()->tryTakeCourse($task['courseId']);

        $canLearnTask = $this->createCourseStrategy($course['id'])->canLearnTask($task);
        return $canLearnTask;
    }

    public function isTaskLearned($taskId)
    {
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

        return empty($taskResult) ? false : ('finish' == $taskResult['status']);
    }

    public function getMaxSeqByCourseId($courseId)
    {
        return $this->getTaskDao()->getMaxSeqByCourseId($courseId);
    }

    public function getTaskByCourseIdAndActivityId($courseId, $activityId)
    {
        return $this->getTaskDao()->getTaskByCourseIdAndActivityId($courseId, $activityId);
    }

    public function findTasksByChapterId($chapterId)
    {
        return $this->getTaskDao()->findByChapterId($chapterId);
    }

    public function findTasksFetchActivityByChapterId($chapterId)
    {
        $tasks = $this->findTasksByChapterId($chapterId);

        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities  = $this->getActivityService()->findActivities($activityIds);
        $activities  = ArrayToolkit::index($activities, 'id');

        array_walk($tasks, function (&$task) use ($activities) {
            $activity         = $activities[$task['activityId']];
            $task['activity'] = $activity;
        });
        return $tasks;
    }

    /**
     *
     * 自由式
     * 1.获取所有的在学中的任务结果，如果为空，则学员学员未开始学习或者已经学完，取第一个任务作为下一个学习任务，
     * 2.如果不为空，则按照任务序列返回第一个作为下一个学习任务
     * 任务式
     * 1.获取所有的在学中的任务结果，如果为空，则学员学员未开始学习或者已经学完，取第前三个作为任务，
     * 2.如果不为空，则取关联的三个。
     *
     * 自由式和任务式的逻辑由任务策略完成
     * @param  $courseId
     * @return array       tasks
     */
    public function findToLearnTasksByCourseId($courseId)
    {
        list($course) = $this->getCourseService()->tryTakeCourse($courseId);
        $toLearnTasks = array();
        if ($course['learnMode'] == 'freeMode') {
            $toLearnTasks[] = $this->getToLearnTaskWithFreeMode($courseId);
        } elseif ($course['learnMode'] == 'lockMode') {
            list($tasks, $toLearnTasks) = $this->getToLearnTasksWithLockMode($courseId);

        } else {
            return $toLearnTasks;
        }

        $activityIds = ArrayToolkit::column($toLearnTasks, 'activityId');

        $activities = $this->getActivityService()->findActivities($activityIds);
        $activities = ArrayToolkit::index($activities, 'id');

        $taskIds     = ArrayToolkit::column($toLearnTasks, 'id');
        $taskResults = $this->getTaskResultService()->findUserTaskResultsByTaskIds($taskIds);

        //设置任务是否解锁
        foreach ($toLearnTasks as &$task) {
            $task['activity'] = $activities[$task['activityId']];
            foreach ($taskResults as $key => $result) {
                if ($result['courseTaskId'] != $task['id']) {
                    continue;
                }
                $task['result'] = $result;
            }
            if ($course['learnMode'] == 'lockMode') {
                $task = $this->setTaskLockStatus($tasks, $task);
            }
        }

        return $toLearnTasks;
    }

    protected function getToLearnTaskWithFreeMode($courseId)
    {
        $taskResults = $this->getTaskResultService()->findUserProgressingTaskResultByCourseId($courseId);
        if (empty($taskResults)) {
            $minSeq      = $this->getTaskDao()->getMinSeqByCourseId($courseId);
            $toLearnTask = $this->getTaskDao()->getByCourseIdAndSeq($courseId, $minSeq);
        } else {
            $latestTaskResult = array_shift($taskResults);
            $latestLearnTask  = $this->getTask($latestTaskResult['courseTaskId']); //获取最新学习未学完的课程
            $tasks            = $this->getTaskDao()->search(array('seq_GE' => $latestLearnTask['seq'], 'courseId' => $courseId), array('seq' => 'ASC'), 0, 2);
            $toLearnTask      = array_pop($tasks); //如果当正在学习的是最后一个，则取当前在学的任务
        }
        return $toLearnTask;
    }

    protected function getToLearnTasksWithLockMode($courseId)
    {
        $toLearnTaskCount = 3;
        $taskResult       = $this->getTaskResultService()->getUserLatestFinishedTaskResultByCourseId($courseId);
        $toLearnTasks     = array();

        //取出所有的任务
        $taskCount = $this->countTasksByCourseId($courseId);
        $tasks     = $this->getTaskDao()->search(array('courseId' => $courseId), array('seq' => 'ASC'), 0, $taskCount);


        if (empty($taskResult)) {
            $toLearnTasks = $this->getTaskDao()->search(array('courseId' => $courseId), array('seq' => 'ASC'), 0, $toLearnTaskCount);
            return array($tasks, $toLearnTasks);
        }

        if (count($tasks) <= $toLearnTaskCount) {
            $toLearnTasks = $tasks;
            return array($tasks, $toLearnTasks);
        }

        $previousTask = null;
        //向后取待学习的三个任务
        foreach ($tasks as $task) {
            if ($task['id'] == $taskResult['courseTaskId']) {
                $toLearnTasks[] = $task;
                $previousTask   = $task;
            }
            if ($previousTask && $task['seq'] > $previousTask['seq'] && count($toLearnTasks) < $toLearnTaskCount) {
                array_push($toLearnTasks, $task);
                $previousTask = $task;
            }
        }

        //向后去待学习的任务不足3个，向前取。
        $reverseTasks = array_reverse($tasks);
        if (count($toLearnTasks) < $toLearnTaskCount) {
            foreach ($reverseTasks as $task) {
                if ($task['id'] == $taskResult['courseTaskId']) {
                    $previousTask = $task;
                }
                if ($previousTask && $task['seq'] < $previousTask['seq'] && count($toLearnTasks) < $toLearnTaskCount) {
                    array_unshift($toLearnTasks, $task);
                    $previousTask = $task;
                }
            }
        }
        return array($tasks, $toLearnTasks);
    }

    public function trigger($id, $eventName, $data = array())
    {
        $task = $this->getTask($id);
        $this->getActivityService()->trigger($task['activityId'], $eventName, $data);
        return $this->getTaskResultService()->getUserTaskResultByTaskId($id);
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    protected function createCourseStrategy($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            throw $this->createNotFoundException('course does not exist');
        }

        return StrategyContext::getInstance()->createStrategy($course['isDefault'], $this->biz);
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->biz->service('Task:TaskResultService');
    }

    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @param $tasks
     * @param $task
     * @return mixed
     */
    protected function setTaskLockStatus($tasks, $task)
    {
        $preTasks = $this->getPreTask($tasks, $task);
        if (empty($preTasks)) {
            $task['lock'] = false;
        }

        $finish       = $this->isPreTasksIsFinished($preTasks);
        $task['lock'] = !$finish;

        //选修任务不需要判断解锁条件
        if ($task['isOptional']) {
            $task['lock'] = false;
        }

        if ($task['type'] == 'live') {
            $task['lock'] = false;
        }

        if ($task['type'] == 'testpaper' and $task['startTime']) {
            $task['lock'] = false;
        }
        return $task;
    }
}