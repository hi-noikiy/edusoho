webpackJsonp(["app/js/course-manage/teachers/index"],{0:function(e,t){e.exports=jQuery},"55e73d7afebf9c74b73e":function(e,t,a){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}function o(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function r(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function i(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}function s(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}function l(e,t,a,n){var o,r={itemId:Math.random(),nickname:t[n.nickname],isVisible:1==t[n.isVisible],avatar:t[n.avatar],seq:a,id:t[n.id],outputValue:(o={},s(o,n.id,t[n.id]),s(o,n.isVisible,t[n.isVisible]),o)};e.push(r)}function u(e,t){e.map(function(a,n){a.itemId==t&&(e[n].isVisible=!e[n].isVisible,e[n].outputValue.isVisible=e[n].isVisible?1:0)})}Object.defineProperty(t,"__esModule",{value:!0});var c=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var a=arguments[t];for(var n in a)Object.prototype.hasOwnProperty.call(a,n)&&(e[n]=a[n])}return e},p=function(){function e(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}return function(t,a,n){return a&&e(t.prototype,a),n&&e(t,n),t}}(),d=a("33a776824bec073629e5"),f=n(d),m=a("26fa658edb0135ccf5db"),b=n(m),h=a("d0399763e3c229c64154"),y=n(h),g=function(e){function t(e){o(this,t);var a=r(this,(t.__proto__||Object.getPrototypeOf(t)).call(this,e));return a.onChecked=function(e){var t=e.currentTarget.value;u(a.state.dataSourceUi,t),a.setState({dataSourceUi:a.state.dataSourceUi})},a.addItem=function(e,t){t&&(a.props.replaceItem&&(a.state.dataSourceUi=[]),l(a.state.dataSourceUi,t,a.state.dataSourceUi.length+1,a.props),a.setState({dataSourceUi:a.state.dataSourceUi}))},a}return i(t,e),p(t,[{key:"componentWillMount",value:function(){var e=this;this.state={dataSourceUi:[]},this.props.dataSource.map(function(t,a){l(e.state.dataSourceUi,t,a+1,e.props)})}},{key:"getChildContext",value:function(){return{addable:this.props.addable,searchable:this.props.searchable,sortable:this.props.sortable,listClassName:this.props.listClassName,inputName:this.props.inputName,showCheckbox:this.props.showCheckbox,showDeleteBtn:this.props.showDeleteBtn,checkBoxName:this.props.checkBoxName,onChecked:this.onChecked,removeItem:this.removeItem,sortItem:this.sortItem,addItem:this.addItem,dataSourceUi:this.state.dataSourceUi}}},{key:"getList",value:function(){return f.default.createElement(y.default,null)}}]),t}(b.default);t.default=g,g.propTypes=c({},b.default.propTypes,{id:f.default.PropTypes.string,nickname:f.default.PropTypes.string,avatar:f.default.PropTypes.string,isVisible:f.default.PropTypes.string,replaceItem:f.default.PropTypes.bool,showCheckbox:f.default.PropTypes.bool,showDeleteBtn:f.default.PropTypes.bool}),g.defaultProps=c({},b.default.defaultProps,{id:"id",nickname:"nickname",avatar:"avatar",isVisible:"isVisible",replaceItem:!1,showCheckbox:!0,showDeleteBtn:!0}),g.childContextTypes=c({},b.default.childContextTypes,{showCheckbox:f.default.PropTypes.bool,showDeleteBtn:f.default.PropTypes.bool,checkBoxName:f.default.PropTypes.string,onChecked:f.default.PropTypes.func})},"7731765f2fdea11c122d":function(e,t,a){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}var o=a("5fdcf1aea784583ca083"),r=n(o),i=a("33a776824bec073629e5"),s=n(i),l=a("55e73d7afebf9c74b73e"),u=n(l),c=a("8f840897d9471c8c1fbd"),p=(n(c),a("b334fd7e4c5a19234db2")),d=n(p);r.default.render(s.default.createElement(u.default,{addable:!0,dataSource:$("#course-teachers").data("init-value"),outputDataElement:"teachers",inputName:"ids[]",searchable:{enable:!0,url:$("#course-teachers").data("query-url")+"?q="}}),document.getElementById("course-teachers")),$(".js-btn-save").on("click",function(e){"[]"!==$("input[name=teachers]").val()?$("#teachers-form").submit():(0,d.default)("warning",Translator.trans("course.manage.min_teacher_num_error_hint"))})},d0399763e3c229c64154:function(e,t,a){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}function o(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function r(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function i(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}Object.defineProperty(t,"__esModule",{value:!0});var s=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var a=arguments[t];for(var n in a)Object.prototype.hasOwnProperty.call(a,n)&&(e[n]=a[n])}return e},l=function(){function e(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}return function(t,a,n){return a&&e(t.prototype,a),n&&e(t,n),t}}(),u=a("33a776824bec073629e5"),c=n(u),p=a("8f840897d9471c8c1fbd"),d=(n(p),a("3fb32ce3bf28bfad7e02"),a("fdfc24440b4845bd47af")),f=n(d),m=function(e){function t(e){return o(this,t),r(this,(t.__proto__||Object.getPrototypeOf(t)).call(this,e))}return i(t,e),l(t,[{key:"render",value:function(){var e=this,t=this.context,a=t.dataSourceUi,n=t.listClassName,o=t.sortable,r=t.showCheckbox,i=t.showDeleteBtn,s=t.checkBoxName,l=t.inputName,u="";return a.length>0&&(u="list-group"),c.default.createElement("ul",{id:this.listId,className:"multi-list sortable-list "+u+" "+n},a.map(function(t,a){return c.default.createElement("li",{className:"list-group-item",id:t.itemId,key:a,"data-seq":t.seq},o&&c.default.createElement("i",{className:"es-icon es-icon-yidong mrl color-gray inline-block vertical-middle"}),c.default.createElement("img",{className:"avatar-sm avatar-sm-square mrm",src:t.avatar}),c.default.createElement("span",{className:"label-name text-overflow inline-block vertical-middle"},t.nickname),c.default.createElement("label",{className:r?"":"hidden"},c.default.createElement("input",{type:"checkbox",name:s+t.id,checked:t.isVisible,onChange:function(t){return e.context.onChecked(t)},value:t.itemId}),Translator.trans("course.manage.teacher_display_label")),c.default.createElement("a",{className:i?"pull-right link-gray mtm":"hidden",onClick:function(t){return e.context.removeItem(t)},"data-item-id":t.itemId},c.default.createElement("i",{className:"es-icon es-icon-close01 text-12"})),c.default.createElement("input",{type:"hidden",name:l,value:t.id}))}))}}]),t}(f.default);t.default=m,m.contextTypes=s({},f.default.contextTypes,{showCheckbox:c.default.PropTypes.bool,showDeleteBtn:c.default.PropTypes.bool,checkBoxName:c.default.PropTypes.string,onChecked:c.default.PropTypes.func})},d5edd94aba2c5d017a51:function(e,t,a){var n=a("d5edd94aba2c5d017a5d");"string"==typeof n&&(n=[[e.i,n,""]]);var o={insertAt:"top",hmr:!0};o.transform=void 0;a("3296c0d42e5b7cde21ad")(n,o);n.locals&&(e.exports=n.locals)},d5edd94aba2c5d017a5d:function(e,t,a){t=e.exports=a("e7f1add7f34e416618de")(void 0),t.push([e.i,".multi-group {\n  position: relative;\n}\n.multi-group .multi-list {\n  margin-bottom: 0;\n}\n.multi-group .list-group {\n  margin-bottom: 20px;\n}\n.multi-group .list-group .list-group-item {\n  padding: 10px 40px;\n  cursor: move;\n}\n.multi-group .list-group .drag-icon-btn,\n.multi-group .list-group .move-icon-btn {\n  position: absolute;\n}\n.multi-group .list-group .drag-icon-btn {\n  left: 10px;\n  top: 14px;\n}\n.multi-group .list-group .move-icon-btn {\n  right: 10px;\n  top: 10px;\n}\n.multi-group .list-group .move-icon-btn i {\n  font-size: 14px;\n}\n.multi-group .list-group .placeholder {\n  margin-bottom: 0;\n}\n.multi-group .list-group .label-name {\n  width: 160px;\n}\n",""])}},["7731765f2fdea11c122d"]);