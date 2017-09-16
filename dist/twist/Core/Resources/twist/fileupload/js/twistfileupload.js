!function(e,t){"object"==typeof exports&&"undefined"!=typeof module?module.exports=t():"function"==typeof define&&define.amd?define(t):e.twistfileupload=t()}(this,function(){"use strict";function e(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var t=function(){function e(e,t){for(var s=0;s<t.length;s++){var n=t[s];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}return function(t,s,n){return s&&e(t.prototype,s),n&&e(t,n),t}}(),s=function(){function s(t){return e(this,s),this.el=t,this}return t(s,[{key:"show",value:function(){this.toggle(!0)}},{key:"hide",value:function(){this.toggle()}},{key:"toggle",value:function(e){var t=this.el.getAttribute("data-initialdisplay"),s=this.el.style.display,n=(window.getComputedStyle?getComputedStyle(this.el,null):this.el.currentStyle).display;e?(t||"none"!==s||(this.el.style.display=""),""===this.el.style.display&&"none"===n&&(t=t||defaultDisplay(this.el.nodeName))):(s&&"none"!==s||"none"!==n)&&this.el.setAttribute("data-initialdisplay","none"===n?s:n),e&&"none"!==this.el.style.display&&""!==this.el.style.display||(this.el.style.display=e?t||"":"none")}}],[{key:"create",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:[],s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},n=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"",i=document.createElement(e);if(t)try{i.className=t.join(" ")}catch(e){"string"==typeof t&&(i.className=t)}if(s)for(var r in s)s.hasOwnProperty(r)&&i.setAttribute(r,s[r]);return n&&(i.innerHTML=n),i}}]),s}();return function(){function n(t,i,r){var o=this,a=arguments.length>3&&void 0!==arguments[3]&&arguments[3],l=arguments.length>4&&void 0!==arguments[4]?arguments[4]:{};e(this,n);var u="string"==typeof(new XMLHttpRequest).responseType&&"withCredentials"in new XMLHttpRequest;if(u){var p=new XMLHttpRequest;p.open("GET","/");try{p.responseType="arraybuffer"}catch(e){u=!1}}this.settings=Object.assign({abortable:!0,acceptTypes:[],acceptExtensions:[],counter:!0,debug:!1,dragdrop:null,dropableclass:"twistupload-dropable",hoverclass:"twistupload-hover",invalidtypemessage:"This file type is not permitted",onabort:function(){},onclear:function(){},oncompletefile:function(){},oncompletequeue:function(){},onerror:function(){},oninvalidtype:function(){},onprogress:function(){},onstart:function(){},previewsize:128,previewsquare:!0},l),this.id=t,this.elements={CancelUpload:s.create("button","",{},"Cancel"),Count:s.create("span"),CountTotal:s.create("span"),CountWrapper:s.create("span"),Input:s.create("input","",function(){var e={type:"file",name:a?r+"[]":r};return a&&(e.multiple="multiple"),e}()),List:s.create("ul"),Progress:s.create("progress","",{value:"0",max:"100"}),ProgressWrapper:s.create("span"),Pseudo:s.create("input","",{type:"hidden",value:""}),Wrapper:document.getElementById(t)},this.events={},this.multiple=a,this.queue=[],this.queueCount=0,this.queueSize=0,this.queueUploadedCount=0,this.queueUploadedSize=0,this.request=new XMLHttpRequest,this.uploaded=[],this.uri="/"+i.replace(/^\//,"").replace(/\/$/,""),this.addMarkup(),this.addDragAndDropListeners(),u?this.elements.Input.addEventListener("change",function(e,t){o.upload(e,t)}):(this.hideProgress(),console.warn("Your browser does not support AJAX uploading","warn",!0))}return t(n,[{key:"addMarkup",value:function(){this.elements.ProgressWrapper.appendChild(this.elements.Progress),this.elements.ProgressWrapper.appendChild(this.elements.CancelUpload),this.multiple&&(this.elements.CountWrapper.appendChild(this.elements.Count),this.elements.CountWrapper.insertAdjacentHTML("beforeend","/"),this.elements.CountWrapper.appendChild(this.elements.CountTotal),this.elements.ProgressWrapper.appendChild(this.elements.CountWrapper)),this.elements.Wrapper.appendChild(this.elements.Input),this.elements.Wrapper.appendChild(this.elements.Pseudo),this.elements.Wrapper.appendChild(this.elements.ProgressWrapper),this.elements.Wrapper.appendChild(this.elements.List),this.hideProgress()}},{key:"addDragAndDropListeners",value:function(){var e=this,t=this.elements.Wrapper;this.settings.dragdrop&&(t=document.getElementById(this.settings.dragdrop)),t.ondrop=function(s){s.preventDefault(),e.upload(s,s.target.files||s.dataTransfer.files),t.classList.remove(e.settings.hoverclass),t.classList.remove(e.settings.dropableclass)},t.ondragstart=function(){return t.classList.add(e.settings.dropableclass),!1},t.ondragover=function(){return t.classList.add(e.settings.hoverclass),!1},t.ondragleave=function(){return t.classList.remove(e.settings.hoverclass),!1},t.ondragend=function(){return t.classList.remove(e.settings.hoverclass),t.classList.remove(e.settings.dropableclass),!1}}},{key:"upload",value:function(e,t){var i=this;try{if(e){var r=t||(e.target||e.srcElement).files;this.queue.push.apply(this.queue,r),this.queueCount+=r.length;for(var o=0,a=r.length;o<a;o++)this.queueSize+=parseInt(r[o].size);this.elements.CountTotal&&(this.elements.CountTotal.innerText=this.queueCount),console.log("Added "+r.length+" files to the queue","info")}if(this.queue.length){var l=this.queue[0],u=l.name,p=l.type,d=u.substr(u.lastIndexOf(".")+1).toLowerCase(),h=parseInt(l.size),c=new FileReader({blob:!0}),f=!this.settings.acceptTypes.length&&!this.settings.acceptExtensions.length;if(!f){var g=!0,v=!1,m=void 0;try{for(var y,q=this.settings.acceptTypes[Symbol.iterator]();!(g=(y=q.next()).done);g=!0){var w=y.value;if(new RegExp("^"+w+"$","gi").test(p)){f=!0;break}}}catch(e){v=!0,m=e}finally{try{!g&&q.return&&q.return()}finally{if(v)throw m}}}if(!f){var C=!0,b=!1,L=void 0;try{for(var U,P=this.settings.acceptExtensions[Symbol.iterator]();!(C=(U=P.next()).done);C=!0)if(d===U.value){f=!0;break}}catch(e){b=!0,L=e}finally{try{!C&&P.return&&P.return()}finally{if(b)throw L}}}if(f)this.settings.onstart(l),this.showProgress(),this.elements.Count&&(this.elements.Count.innerText=this.queueUploadedCount+1),1===this.queueCount?(this.elements.Progress&&this.elements.Progress.removeAttribute("value"),new s(this.elements.CountWrapper).hide()):this.elements.CountWrapper&&new s(this.elements.CountWrapper).show(),c.addEventListener("load",function(e){i.request.onreadystatechange=function(){switch(i.request.status){case 200:if(4===i.request.readyState){console.info("Uploaded "+u+" ("+n.prettySize(h)+")"),i.queue.shift(),i.queueUploadedCount++,i.queueUploadedSize+=h;var e=JSON.parse(i.request.responseText);i.queue.length?(i.multiple?i.uploaded.push(e):i.uploaded=[e.form_value],i.updateUploadedList(),window.twist.debug&&window.twist.debug.logFileUpload(l,e),i.settings.oncompletefile(e,l),i.upload()):(i.hideProgress(),console.info("Finished uploading "+i.queueUploadedCount+" files ("+n.prettySize(i.queueUploadedSize)+")","info"),i.queueCount=0,i.queueSize=0,i.queueUploadedCount=0,i.queueUploadedSize=0,i.clearInput(),i.multiple?i.uploaded.push(e):i.uploaded=[e],i.updateUploadedList(),window.twist.debug&&window.twist.debug.logFileUpload(l,e),i.settings.oncompletefile(e,l),i.settings.oncompletequeue())}break;case 403:console.error("Permission denied","error"),i.queue.shift(),i.queueCount--,i.queueSize--,i.settings.onerror(l),i.queue.length?i.upload():i.hideProgress();break;case 404:console.error("Invalid function call","error"),i.queue.shift(),i.queueCount--,i.queueSize--,i.settings.onerror(l),i.queue.length?i.upload():i.hideProgress()}},i.request.onprogress=function(e){if(e.lengthComputable){if(i.elements.Progress){var t=Math.round(e.loaded/e.total*100);i.elements.Progress.value=t,console.log(n.prettySize(e.loaded)+"/"+n.prettySize(e.total)+" ("+t+"%)")}i.settings.onprogress(l,e.loaded,e.total)}},i.request.upload.onprogress=i.request.onprogress,i.request.addEventListener("load",function(){},!1),i.request.addEventListener("error",function(){i.queue.length&&(i.hideProgress(),i.queue=[],i.queueCount=0,i.queueSize=0,i.queueUploadedCount=0,i.queueUploadedSize=0,i.settings.onerror(l),console.error("An error occurred","error"))},!1),i.request.addEventListener("abort",function(){i.queue.length&&(i.hideProgress(),i.queue=[],i.queueCount=0,i.queueSize=0,i.queueUploadedCount=0,i.queueUploadedSize=0,i.settings.onabort(l),console.error("Upload aborted","warning"))},!1),i.request.open("PUT",i.uri,!0),i.request.setRequestHeader("Accept",'"text/plain; charset=iso-8859-1", "Content-Type": "text/plain; charset=iso-8859-1"'),i.request.setRequestHeader("Twist-File",u),i.request.setRequestHeader("Twist-Length",h),i.request.setRequestHeader("Twist-UID",i.id),i.request.send(c.result)}),c.readAsArrayBuffer(l);else{var k=this.queue.shift();this.elements.Input.value="",this.settings.oninvalidtype(k,this.acceptTypes,this.acceptExtentions),console.error(u+" ("+p+") is not in the list of allowed types","warn"),this.acceptTypes.length&&console.info("Allowed MIME types: "+this.acceptTypes.join(", ")),this.acceptExtentions.length&&console.info("Allowed file extensions: "+this.acceptExtentions.join(", ")),this.clearInput()}}}catch(e){console.log(this),this.hideProgress(),this.settings.onerror(this.queue[0]),this.settings.onabort(this.queue[0]),this.queue=[],this.queueCount=0,this.queueSize=0,this.queueUploadedCount=0,this.queueUploadedSize=0,console.error(e,"error")}}},{key:"showProgress",value:function(){new s(this.elements.Input).hide(),new s(this.elements.ProgressWrapper).show(),this.elements.CancelUpload&&this.elements.CancelUpload.addEventListener("click",this.cancelUpload)}},{key:"hideProgress",value:function(){new s(this.elements.Input).show(),new s(this.elements.ProgressWrapper).hide(),this.elements.CancelUpload&&this.elements.CancelUpload.removeEventListener("click",this.cancelUpload)}},{key:"clearInput",value:function(){this.elements.Input.value="",this.elements.Input.value&&(this.elements.Input.type="text",this.elements.Input.type="file"),this.elements.Pseudo.value="",this.settings.onclear()}},{key:"cancelUpload",value:function(){this.request.abort()}},{key:"updateUploadedList",value:function(){var e=this,t=[];this.elements.List.innerHTML="",console.log(this.uploaded);var n=!0,i=!1,r=void 0;try{for(var o,a=this.uploaded[Symbol.iterator]();!(n=(o=a.next()).done);n=!0){var l=o.value,u=l.uri_preview,p="",d=["file/name","file/size","file_type"],h="thumb-"+this.settings.previewsize;t.push(l.form_value),this.settings.previewsquare&&(h="square-"+h),l.support&&l.support[h]&&(u=l.support[h]);for(var c in d){var f=d[c],g=void 0;if(-1!==f.indexOf("/")){var v=f.split("/"),m=l[v[0]]||null;if(v.shift(),m){for(var y in v)m=m[v[y]]||null;g=m||null}}else g=l[f]||null;p+='<li data-key="'+f+'"><span>'+f.replace(/[\/_]/g," ")+" :</span>"+g+"</li>"}var q=s.create("li","twistupload-file-list-item"),w=s.create("img","",{src:u}),C=s.create("ul","twistupload-file-list-item-info",{},p),b=s.create("button","",{},"Remove");b.addEventListener("click",function(t){return function(){e.uploaded.splice(e.uploaded.indexOf(t),1),e.updateUploadedList()}}(l)),q.appendChild(w),q.appendChild(C),q.appendChild(b),this.elements.List.appendChild(q)}}catch(e){i=!0,r=e}finally{try{!n&&a.return&&a.return()}finally{if(i)throw r}}this.elements.Pseudo.value=t.join(",")}},{key:"on",value:function(e,t){arguments.length>2&&void 0!==arguments[2]&&arguments[2]}},{key:"off",value:function(e,t){}},{key:"trigger",value:function(e){}}],[{key:"prettySize",value:function(e){for(var t=["B","kB","MB","GB","TB","PB","EB","ZB","YB"],s=0;t[s]&&e>Math.pow(1024,s+1);)s++;return this.round(e/Math.pow(1024,s),s>1?2:0)+t[s]}},{key:"round",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:0;return 0===t?parseInt(Math.round(e*Math.pow(10,t))/Math.pow(10,t)):parseFloat(Math.round(e*Math.pow(10,t))/Math.pow(10,t))}}]),n}()});
//# sourceMappingURL=twistfileupload.js.map
