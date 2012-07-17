/*
 jQuery password123: iPhone Style Passwords Plugin - v1.5 - 2/1/2011
 http://timmywillison.com/samples/password123/
 Copyright (c) 2011 timmy willison
 Dual licensed under the MIT and GPL licences.
 http://timmywillison.com/licence/
*/
(function(e,n,l,g){e.fn.password123=function(a){var b=e.data(this[0],"password123"),c=Array.prototype.slice.call(arguments,1);if(b&&typeof a==="string"&&a.charAt(0)!=="_"&&b[a]){c.unshift(this);return b[a].apply(b,c)}return this.map(function(d,f){return new i._init(f,a)})};var m=n.clearTimeout,o=0,i={_init:function(a,b){if(!a.type==="password")return a;var c=this;c.options=e.extend({character:"&#8226;",delay:2E3,prefix:"iField",placeholder:true,placeholderClass:"place",maskInitial:false},b);c.encodedChar=
e("<div>"+c.options.character+"</div>").text();c.$field=c._replaceField(a).bind("textchange",function(){c._letterChange.call(c)});c.options.placeholder&&c._bindPlaceholder();c.options.maskInitial&&c.$field.keyup();return c.$field[0]},_replaceField:function(a){var b=e(a),c=b.attr("placeholder")||g,d=this.options.prefix+o++,f=b.val()||(this.options.placeholder?c||"":"");d={"class":this.options.placeholder&&c!==g&&(f===c||f==="")?a.className+" "+this.options.placeholderClass:a.className,id:d,value:f,
placeholder:this.options.placeholder?g:c};for(var j=["size","tabindex","readonly","disabled","maxlength"],h=0;h<j.length;h++){var k=b.attr(j[h]);d[j[h]]=k&&k>-1?k:g}this.$hidden=e('<input type="hidden"/>').attr({name:b.attr("name"),id:a.id,"class":a.className,disabled:b.attr("disabled")}).replaceAll(b).val(f!==c?f:"");this.$oldField=b;return e('<input type="text"/>').attr(d).insertAfter(this.$hidden).data({value:b.val()||"",placeholder:c,newVal:f,password123:this})},_letterChange:function(){var a=
this.$field.val();if(a.length>this.$field.data("value").length)this.$field.data("value",this._fieldChange());else if(a.length<this.$field.data("value").length){m(this.last);var b=this.$hidden.val();if(a.length<b.length-1)b=b.substr(0,a.length);else{var c=this._getCursorPosition();b=b.length>c+1&&c>-1?b.slice(0,c).concat(b.slice(c+1)):b.slice(0,c)}this.$field.data({value:a,newVal:b});b!==this.$field.data("placeholder")&&this.$hidden.val(b)}return this},_fieldChange:function(){var a=this;m(a.last);
var b=a.$field.val(),c=b.length,d=a.$hidden.val(),f=a._getCursorPosition();d=d.length>f+1&&f>-1?d.substr(0,f-1)+b.charAt(f-1)+d.substr(f-1):d+b.charAt(c-1);a.$field.data("newVal",d);d!==a.$field.data("placeholder")&&a.$hidden.val(d);if(c>1){for(d=0;d<c-1;d++)b=b.replace(b.charAt(d),a.encodedChar);a.$field.val(b)}if(c>0)a.last=setTimeout(function(){f=a._getCursorPosition();b=a.$field.val();b=b.replace(b.charAt(c-1),a.encodedChar);a.$field.val(b).data("value",b);f!=c&&a._setCursorPosition(f)},a.options.delay);
f!=c&&a._setCursorPosition(f);return b},_bindPlaceholder:function(){var a=this,b=a.$field.data("placeholder");b!==g?a.$field.bind({"focus.password123":function(){if(a.$field.data("newVal")===b){a.$field.val("").removeClass(a.options.placeholderClass).data("newVal","");a.$hidden.val("")}},"blur.password123":function(){if(b!==g&&a.$field.val()===""){a.$field.val(b).addClass(a.options.placeholderClass).data("newVal",b);a.options.maskInitial&&a.$field.keyup()}}}):a.$field.keyup();return a},_changePlaceholder:function(a){if(a&&
!this.options.placeholder)return this._bindPlaceholder();else!a&&this.options.placeholder&&this.$field.focus().unbind(".password123").blur();return this},_changePrefix:function(a){var b=this.options.prefix;this.$field.attr("id",this.$field.attr("id").replace(b,a));return this},_getCursorPosition:function(){var a=this.$field[0];if(a!=null)if(l.selection){a.focus();var b=l.selection.createRange();b.moveStart("character",-a.value.length);return b.text.length}else if(a.selectionStart!==g)return a.selectionStart;
return-1},_setCursorPosition:function(a){var b=this.$field[0];if(b!=null)if(b.createTextRange){b=b.createTextRange();b.move("character",a);b.select()}else if(b.setSelectionRange){b.focus();b.setSelectionRange(a,a)}else b.focus()},_setOptions:function(a){var b=this;e.each(a,function(c,d){switch(c){case "placeholder":b._changePlaceholder(d==="false"?false:d);break;case "prefix":b._changePrefix(d)}b.options[c]=d});return this},destroy:function(a){return this.$oldField.val(this.$hidden.remove().val()).replaceAll(a)},
option:function(a,b,c){if(!b)return e.extend({},this.options);var d=b;if(typeof b==="string"){if(c===g)return this.options[b];d={};d[b]=c}this._setOptions(d);return a}};i._init.prototype=i;e.event.special.textchange={setup:function(){e(this).bind("keyup.textchange",e.event.special.textchange.handler);e(this).bind("cut.textchange paste.textchange input.textchange",e.event.special.textchange.delayedHandler)},teardown:function(){e(this).unbind(".textchange")},handler:function(){e.event.special.textchange.triggerIfChanged(e(this))},
delayedHandler:function(){var a=e(this);setTimeout(function(){e.event.special.textchange.triggerIfChanged(a)},25)},triggerIfChanged:function(a){var b=a.val();if(a.val()!==a.data("lastValue")){var c=e.data(a[0],"password123");if(b.length>1&&b.indexOf(c.encodedChar)===-1){b=b.substr(0,b.length-1);a.data("value",b);c.$hidden.val(b)}a.trigger("textchange",a.data("lastValue"));a.data("lastValue",a.val())}}}})(jQuery,this,this.document);