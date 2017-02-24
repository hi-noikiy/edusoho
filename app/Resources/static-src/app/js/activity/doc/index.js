import DocPlayer from '../../../common/doc-player';
let $element = $('#document-content');
let watermarkUrl = $element.data('watermark-url');

console.log(watermarkUrl);

if(watermarkUrl) {
  console.log('watermarkUrl');
  $.get(watermarkUrl, function(watermark) {
    console.log(watermark);
    initDocPlayer(watermark);
  });
}else {
  initDocPlayer('');
}

function initDocPlayer(contents) {
  let doc = new DocPlayer({
    element: $element,
    swfUrl: $element.data('swf'),
    pdfUrl: $element.data('pdf'),
    watermarkOptions: {
      contents,
      xPosition: 'center',
      yPosition: 'center',
      rotate: 45,
    }
  });
}
