/*
 ### jQuery Multiple File Upload Plugin v 1.29 - 2008-06-26 ###
 * http://www.fyneworks.com/ - diego@fyneworks.com
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 ###
 Project: http://jquery.com/plugins/project/MultiFile/
 Website: http://www.fyneworks.com/jquery/multiple-file-upload/
*/

/*# AVOID COLLISIONS #*/
;if(jQuery) (function($){
/*# AVOID COLLISIONS #*/

 // extend jQuery - $.MultiFile hook
 $.extend($, {
  MultiFile: function( o /* Object */ ){
   //return $("INPUT[@type='file'].multi").MultiFile(o);
   return $("input:file.multi").MultiFile(o);
  }
 });

 //===

 // extend $.MultiFile - default options
 $.extend($.MultiFile, {
  options: {
   accept: '', max: -1,
   // error handling function
   error: function(s){
    if($.blockUI){
     $.blockUI({
      message: s.replace(/\n/gi,'<br/>'),
      css: {
       border:'none', padding:'15px', size:'12.0pt',
       backgroundColor:'#900', color:'#fff',
       opacity:'.8','-webkit-border-radius': '10px','-moz-border-radius': '10px'
      }
     });
     window.setTimeout($.unblockUI, 2000);
    }
    else{
     alert(s);
    }
   },
   // namePattern: $name/$id (from master element), $i (slave count), $g (group count)
   namePattern: '$name',
   // STRING: collection lets you show messages in different languages
   STRING: {
    remove:'remove',
    denied:'You cannot select a $ext file.\nTry again...',
    selected:'File selected: $file',
    duplicate:'This file has already been selected:\n$file'
   }
  }
 });

 //===

 // extend $.MultiFile - global methods
 $.extend($.MultiFile, {


  /**
   * This utility makes it easy to disable all 'empty' file elements in the document before submitting a form.
   * It marks the affected elements so they can be easily re-enabled after the form submission or validation.
   *
   * Returns a jQuery collection of all affected elements.
   *
   * @name disableEmpty
   * @type jQuery
   * @cat Plugins/Multifile
   * @author Diego A. (http://www.fyneworks.com/)
   *
   * @example $.MultiFile.disableEmpty();
   * @param String class (optional) A string specifying a class to be applied to all affected elements - Default: 'mfD'.
   */
  disableEmpty: function(klass){
   var o = [];
   $('input:file').each(function(){ if($(this).val()=='') o[o.length] = this; });
   return $(o).each(function(){ this.disabled = true }).addClass(klass || 'mfD');
  },


 /**
  * This method re-enables 'empty' file elements that were disabled (and marked) with the $.MultiFile.disableEmpty method.
  *
  * Returns a jQuery collection of all affected elements.
  *
  * @name reEnableEmpty
  * @type jQuery
  * @cat Plugins/Multifile
  * @author Diego A. (http://www.fyneworks.com/)
  *
  * @example $.MultiFile.reEnableEmpty();
  * @param String klass (optional) A string specifying the class that was used to mark affected elements - Default: 'mfD'.
  */
  reEnableEmpty: function(klass){
   klass = klass || 'mfD';
   return $('input:file.'+klass).removeClass(klass).each(function(){ this.disabled = false });
  },
  autoIntercept: [ 'submit', 'ajaxSubmit', 'validate' /* array of methods to intercept */ ],
  intercepted: {},
  intercept: function(methods, context, args){
   var method, value; args = args || [];
   if(args.constructor.toString().indexOf("Array")<0) args = [ args ];
   if(typeof(methods)=='function'){
    $.MultiFile.disableEmpty();
    value = methods.apply(context || window, args);
    $.MultiFile.reEnableEmpty();
    return value;
   };
   if(methods.constructor.toString().indexOf("Array")<0) methods = [methods];
   for(var i=0;i<methods.length;i++){
    method = methods[i]+''; // make sure that we have a STRING
    if(method) (function(method){ // make sure that method is ISOLATED for the interception
     $.MultiFile.intercepted[method] = $.fn[method] || function(){};
     $.fn[method] = function(){
      $.MultiFile.disableEmpty();
      value = $.MultiFile.intercepted[method].apply(this, arguments);
      $.MultiFile.reEnableEmpty();
      return value;
     }; // interception
    })(method); // MAKE SURE THAT method IS ISOLATED for the interception
   };// for each method
  }
 });

 //===

 // extend jQuery function library
 $.extend($.fn, {

   // Use this function to clear values of file inputs
   // But this doesn't always work: $(element).val('').attr('value', '')[0].value = '';
   reset: function(){ return this.each(function(){ try{ this.reset(); }catch(e){} }); },

   // MultiFile function
   MultiFile: function( options /* Object */ ){

    //### http://plugins.jquery.com/node/1363
    // utility method to integrate this plugin with others...
    if($.MultiFile.autoIntercept){
     $.MultiFile.intercept( $.MultiFile.autoIntercept /* array of methods to intercept */ );
     $.MultiFile.autoIntercept = null; /* only run this once */
    };

    //===

    // Bind to each element in current jQuery object
    return $(this).each(function(group_count){
     if(this._MultiFile) return; this._MultiFile = true;

       // BUG 1251 FIX: http://plugins.jquery.com/project/comments/add/1251
       // variable group_count would repeat itself on multiple calls to the plugin.
       // this would cause a conflict with multiple elements
       // changes scope of variable to global so id will be unique over n calls
       window.MultiFile = (window.MultiFile || 0) + 1;
       group_count = window.MultiFile;

       // Copy parent attributes - Thanks to Jonas Wagner
       // we will use this one to create new input elements
       var MF = {e:this, E:$(this), clone:$(this).clone()};

       //===

       //# USE CONFIGURATION
       if(typeof options=='number') options = {max:options};
       if(typeof options=='string') options = {accept:options};
       var o = $.extend({},
        $.MultiFile.options,
        options || {},
        ($.meta ? MF.E.data()/*NEW metadata plugin*/ :
        ($.metadata ? MF.E.metadata()/*OLD metadata plugin*/ :
        null/*metadata plugin not available*/)) || {}
       );
       // limit number of files that can be selected?
       if(!(o.max>0) /*IsNull(MF.max)*/){
        o.max = MF.E.attr('maxlength');
        if(!(o.max>0) /*IsNull(MF.max)*/){
         o.max = (String(MF.e.className.match(/\b(max|limit)\-([0-9]+)\b/gi) || ['']).match(/[0-9]+/gi) || [''])[0];
         if(!(o.max>0)) o.max = -1;
         else           o.max = String(o.max).match(/[0-9]+/gi)[0];
        }
       };
       o.max = new Number(o.max);
       // limit extensions?
       o.accept = o.accept || MF.E.attr('accept') || '';
       if(!o.accept){
        o.accept = (MF.e.className.match(/\b(accept\-[\w\|]+)\b/gi)) || '';
        o.accept = new String(o.accept).replace(/^(accept|ext)\-/i,'');
       };

       //===

       // APPLY CONFIGURATION
       $.extend(MF, o || {});
       MF.STRING = $.extend({},$.MultiFile.options.STRING,MF.STRING);

       //===

       //#########################################
       // PRIVATE PROPERTIES/METHODS
       $.extend(MF, {
        n: 0, // How many elements are currently selected?
        slaves: [], files: [],
        instanceKey: MF.e.id || 'MultiFile'+String(group_count), // Instance Key?
        generateID: function(z){ return MF.instanceKey + (z>0 ?'_F'+String(z):''); },
        trigger: function(event, element){
         var handler = MF[event], value = $(element).attr('value');
         if(handler){
          var returnValue = handler(element, value, MF);
          if( returnValue!=null ) return returnValue;
         }
         return true;
        }
       });

       //===

       // Setup dynamic regular expression for extension validation
       // - thanks to John-Paul Bader: http://smyck.de/2006/08/11/javascript-dynamic-regular-expresions/
       if(String(MF.accept).length>1){
        MF.rxAccept = new RegExp('\\.('+(MF.accept?MF.accept:'')+')$','gi');
       };

       //===

       // Create wrapper to hold our file list
       MF.wrapID = MF.instanceKey+'_wrap'; // Wrapper ID?
       MF.E.wrap('<div id="'+MF.wrapID+'"></div>');
       MF.wrapper = $('#'+MF.wrapID+'');

       //===

       // MF MUST have a name - default: file1[], file2[], file3[]
       MF.e.name = MF.e.name || 'file'+ group_count +'[]';

       //===

       // Create a wrapper for the labels
       // * OPERA BUG: NO_MODIFICATION_ALLOWED_ERR ('labels' is a read-only property)
       // this changes allows us to keep the files in the order they were selected
       MF.wrapper.append( '<span id="'+MF.wrapID+'_labels"></span>' );
       MF.labels = $('#'+MF.wrapID+'_labels');

       //===

       // Bind a new element
       MF.addSlave = function( slave, slave_count ){
        // Keep track of how many elements have been displayed
        MF.n++;
        // Add reference to master element
        slave.MF = MF;
        // Count slaves
        slave.i = slave_count;

        // BUG FIX: http://plugins.jquery.com/node/1495
        // Clear identifying properties from clones
        if(slave.i>0) slave.id = slave.name = null;

        // Define element's ID and name (upload components need this!)
        slave.id = slave.id || MF.generateID(slave.i);

        //slave.name = (slave.name || MF.E.attr('name') || 'file');// + (slave.i>0?slave.i:''); // same name as master element
        // 2008-Apr-29: New customizable naming convention (see url below)
        // http://groups.google.com/group/jquery-dev/browse_frm/thread/765c73e41b34f924#
        slave.name = String(MF.namePattern
         /*master name*/.replace(/\$name/gi,MF.E.attr('name'))
         /*master id  */.replace(/\$id/gi,  MF.E.attr('id'))
         /*group count*/.replace(/\$g/gi,   (group_count>0?group_count:''))
         /*slave count*/.replace(/\$i/gi,   (slave_count>0?slave_count:''))
        );

        // Clear value
        $(slave).val('').attr('value','')[0].value = '';

        // If we've reached maximum number, disable input slave
        if( (MF.max > 0) && ((MF.n-1) > (MF.max)) )//{ // MF.n Starts at 1, so subtract 1 to find true count
         slave.disabled = true;
        //};

        // Remember most recent slave
        MF.current = MF.slaves[slave.i] = slave;

        // now let's use jQuery
        slave = $(slave);

        // Triggered when a file is selected
        $(slave).change(function(){

          // Lose focus to stop IE7 firing onchange again
          $(this).blur();

          //# Trigger Event! onFileSelect
          if(!MF.trigger('onFileSelect', this, MF)) return false;
          //# End Event!

          //# Retrive value of selected file from element
          var ERROR = '', v = String(this.value || ''/*.attr('value)*/);

          // check extension
          if(MF.accept){
           if(v!=''){
            if(!v.match(MF.rxAccept)){
             ERROR = MF.STRING.denied.replace('$ext', String(v.match(/\.\w{1,4}$/gi)));
            }
           }
          };

          // Disallow duplicates
          for(var f=0;f<MF.slaves.length;f++){
           if(MF.slaves[f]!=this){
            if(MF.slaves[f].value==v){
             ERROR = MF.STRING.duplicate.replace('$file', v.match(/[^\/\\]+$/gi));
            }
           }
          };

          // Create a new file input element
          //var newEle = $('<input name="'+(MF.E.attr('name') || '')+'" type="file"/>');
          var newEle = $(MF.clone).clone();// Copy parent attributes - Thanks to Jonas Wagner
          //# Let's remember which input we've generated so
          // we can disable the empty ones before submission
          // See: http://plugins.jquery.com/node/1495
          newEle.addClass('MultiFile');

          // Handle error
          if(ERROR!=''){
            // Handle error
            MF.error(ERROR);

            // Clear element value (DOES NOT WORK in some browsers)
            //slave.reset().val('').attr('value', '')[0].value = '';

            // 2007-06-24: BUG FIX - Thanks to Adrian Wróbel <adrian [dot] wrobel [at] gmail.com>
            // Ditch the trouble maker and add a fresh new element
            MF.n--;
            MF.addSlave(newEle[0], this.i);
            slave.parent().prepend(newEle);
            slave.remove();
            return false;
          };

          // Hide this element (NB: display:none is evil!)
          $(this).css({ position:'absolute', top: '-3000px' });

          // Add new element to the form
          MF.labels.before(newEle);//.append(newEle);

          // Update list
          MF.addToList( this );

          // Bind functionality
          MF.addSlave( newEle[0], this.i+1 );

          //# Trigger Event! afterFileSelect
          if(!MF.trigger('afterFileSelect', this, MF)) return false;
          //# End Event!

        }); // slave.change()

       };// MF.addSlave
       // Bind a new element



       // Add a new file to the list
       MF.addToList = function( slave ){

        //# Trigger Event! onFileAppend
        if(!MF.trigger('onFileAppend', slave, MF)) return false;
        //# End Event!

        // Create label elements
        var
         r = $('<div></div>'),
         v = String(slave.value || ''/*.attr('value)*/),
         a = $('<span class="file" title="'+MF.STRING.selected.replace('$file', v)+'">'+v.match(/[^\/\\]+$/gi)[0]+'</span>'),
         b = $('<a href="#'+MF.wrapID+'">'+MF.STRING.remove+'</a>');

        // Insert label
        MF.labels.append(
         r.append('[', b, ']&nbsp;', a)//.prepend(slave.i+': ')
        );

        b.click(function(){

          //# Trigger Event! onFileRemove
          if(!MF.trigger('onFileRemove', slave, MF)) return false;
          //# End Event!

          MF.n--;
          MF.current.disabled = false;

          // Remove element, remove label, point to current
          if(slave.i==0){
           $(MF.current).remove();
           MF.current = slave;
          }
          else{
           $(slave).remove();
          };
          $(this).parent().remove();

          // Show most current element again (move into view) and clear selection
          $(MF.current).css({ position:'', top: '' }).reset().val('').attr('value', '')[0].value = '';

          //# Trigger Event! afterFileRemove
          if(!MF.trigger('afterFileRemove', slave, MF)) return false;
          //# End Event!


          return false;
        });

        //# Trigger Event! afterFileAppend
        if(!MF.trigger('afterFileAppend', slave, MF)) return false;
        //# End Event!

       }; // MF.addToList
       // Add element to selected files list



       // Bind functionality to the first element
       if(!MF.MF) MF.addSlave(MF.e, 0);

       // Increment control count
       //MF.I++; // using window.MultiFile
       MF.n++;

    });
    // each element

   }
   // MultiFile function

 });
 // extend jQuery function library



 /*
  ### Default implementation ###
  The plugin will attach itself to file inputs
  with the class 'multi' when the page loads
 */
 $(function(){ $.MultiFile() });



/*# AVOID COLLISIONS #*/
})(jQuery);
/*# AVOID COLLISIONS #*/
