<!--  
@no_import
-->
<style>


    .bloodline-table {
        width: 100% !important;
        color: #5d5d5d !important;
        font-size: 12px !important;
        font-family: sans-serif, arial !important;
    }
    .bloodline-table th,
    .bloodline-table td {
      padding: 4px !important;
      text-align: left !important;
    }
    .bloodline-table th {
      font-weight: bold !important;
    }

    .bloodline-table thead {
      background: #626262 !important;
      color: #fff !important;
    }
    .bloodline-table thead th {
      vertical-align: bottom;
    }

    #bloodline_report a,
    input.bloodline-submit,
    #toggle-bloodline {
       display: inline-block !important;
       font-size: 12px !important;
        padding: 1px 10px !important;
        margin-bottom: 0 !important;
        font-weight: bold !important;
        line-height: 1.428571429 !important;
        text-align: center !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
        cursor: pointer !important;
        background-image: none !important;
        border: 1px solid transparent !important;
        border-radius: 4px !important;
        color: #000 !important;
        background: #fff !important;
        border-color: #ddd !important;
        text-decoration: none !important;
    }
    #bloodline_report a:hover,
    input.bloodline-submit:hover,
    #toggle-bloodline:hover {
        background: #bfbfbf !important;
    }

    #bloodline_report ul li {
        margin-bottom: 5px !important;
    }
    .bloodline_h2,
    .bloodline_h3 {
        padding-bottom: 10px !important;
        margin-bottom: 10px !important;
        border-bottom: 1px solid #ddd !important;
    }
    .bloodline-container {
         width: 450px !important;
         position: absolute !important;
         top: 0 !important;
         right: 0 !important;
         z-index: 1000;
    }

    #toggle-bloodline {
        position: absolute !important;
        right: 15px !important;
        top: 5px !important;
        z-index: 1000 !important;
        text-indent: -9999px !important;
        padding: 4px 4px 0px !important;
    }
    #bloodline_report {
        width: 400px !important;
        border: 1px solid #999 !important;
        color: #5d5d5d !important;
        font-size: 12px !important;
        font-family: sans-serif, arial !important;
        padding: 20px !important;
        background: #f7f7f7 !important;
    }

    ul.bloodline-ul li a {
        color: #000 !important;
        background: none !important;
        border: none !important;
    }

    .bloodline_pageinfo ul,
    .bloodline_warnings ul,
    .bloodline_errors ul {
        padding-left: 0px !important;
    }

    .bloodline_pageinfo ul li,
    .bloodline_warnings ul li,
    .bloodline_errors ul li { 
        list-style: none !important;
        padding: 5px !important;
    }
    
    .bloodline_pageinfo ul li { 
        background: #d9edf7 !important;
        border: 1px solid #bce8f1 !important;
        color: #60708f !important;
    }

    .bloodline_warnings ul li { 
        background: #fcf8e3 !important;
        border: 1px solid #faebcc !important;
        color: #8a6d6a !important;
    }
    
    .bloodline_errors ul li { 
        background: #f2dede !important;
        border: 1px solid #ebccd1 !important;
        color: #a94442 !important;
    }


    #bloodline_markup_checkboxes label {
        float: right !important;
        text-align: left !important;
        width: 250px !important;
    }

    .bloodline-btn-holder {
        margin-top: 10px !important;
    }

    .bloodline-line {
        border-top: 3px solid #000 !important;
        display: block !important;
        height: 2px !important;
        width: 20px !important;
        margin-bottom: 1px !important;
    }

    #bloodline_footer a {
        border: none !important;
        background: none !important;
        padding: 0px !important;
        color: #454545 !important;
    }
    #bloodline_footer a:hover {
        background: none !important;
        color: #000 !important;
    }

    .bl-close {
        position: absolute;
        top: 5px;
        right: 15px;
        text-decoration: none;
        border: 1px solid #ddd;
        background: #fff;
        padding: 2px 8px;
        font-family: arial;
        color: #bf0707;
    }

</style>

<script>
    //Pulse https://raw.github.com/jsoverson/jquery.pulse.js/master/jquery.pulse.min.js
    (function(t,n){"use strict";var e={pulses:1,interval:0,returnDelay:0,duration:500};t.fn.pulse=function(u,r,a){var i="destroy"===u;return"function"==typeof r&&(a=r,r={}),r=t.extend({},e,r),r.interval>=0||(r.interval=0),r.returnDelay>=0||(r.returnDelay=0),r.duration>=0||(r.duration=500),r.pulses>=-1||(r.pulses=1),"function"!=typeof a&&(a=function(){}),this.each(function(){function e(){return s.data("pulse").stop?void 0:r.pulses>-1&&++c>r.pulses?a.apply(s):(s.animate(u,{duration:r.duration/2,complete:function(){n.setTimeout(function(){s.animate(l,{duration:r.duration/2,complete:function(){n.setTimeout(e,r.interval)}})},r.returnDelay)}}),void 0)}var o,s=t(this),l={},p=s.data("pulse")||{};p.stop=i,s.data("pulse",p);for(o in u)u.hasOwnProperty(o)&&(l[o]=s.css(o));var c=0;e()})}})(jQuery,window,document);

    
</script>

<div class="bloodline-wrap">
    
        
<div class="bloodline-container">
<!--     <a id="toggle-bloodline">
    <div class="bloodline-line">--</div>
    <div class="bloodline-line">--</div>
    <div class="bloodline-line">--</div>
</a> -->
    <a class="bl-close" href="[[+action_url]]">X</a>
    <div id="bloodline_report">
        <br>
        <h1 class="bloodline_h1">Bloodline</h1>
            
        <h2 class="bloodline_h2 bloodline_info_header">Page Info</h2>
        <div class="bloodline_pageinfo">
            <ul class="bloodline-ul">
            [[+bloodline.info]]
            </ul>
        </div>

        <h2 class="bloodline_h2 bloodline_warning_header">Warnings</h2>
        <div class="bloodline_warnings">
            <ul class="bloodline-ul">
            [[+bloodline.warnings]]        
            </ul>
        </div>

        <h2 class="bloodline_h2 bloodline_error_header">Errors</h2>
        <div class="bloodline_errors">
            <ul class="bloodline-ul">
            [[+bloodline.errors]]
            </ul>
        </div>    
        
        <h2 class="bloodline_h2">Tags</h2>
        <div class="bloodline_tags">
            <table class="bloodline-table">
                <thead>
                    <tr>
                        <th>Depth</th>
                        <th>Tag</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    [[+bloodline.tags]]
                </tbody>
            </table>
        </div>
        
        
        <h2 class="bloodline_h2">Add Markup <a href="https://github.com/craftsmancoding/bloodline/wiki/Markup" class="bloodline_info">?</a></h2>
        <form id="bloodline_filter" action="[[+action_url]]" method="get">
            <input type="hidden" name="BLOODLINE" value="1" />
            <input type="hidden" name="type" value="[[+type]]" />
            <input type="hidden" name="field" value="[[+field]]" />
            <input type="hidden" name="obj_id" value="[[+obj_id]]" />
            <label for="bloodline_format" class="bloodline_label">Report Format</label>
            <a href="https://github.com/craftsmancoding/bloodline/wiki/Report-Format" class="bloodline_info" target="_blank">?</a>
            <select id="bloodline_format" name="format">
                <option value="html" [[+html.isselected]]>HTML</option>
                <option value="js" [[+js.isselected]]>Javascript</option>
                <option value="both" [[+both.isselected]]>Both</option>
            </select>
            
            
            
            <div id="bloodline_markup_checkboxes">

            <table class="bloodline-table">
                <tbody>
                    <tr>
                        <td><input id="bloodline_chunk_markup" type="checkbox" name="markup[]" value="chunk" [[+chunk.ischecked]]/></td>
                        <td><div style="height:10px; width:10px; background-color: [[+chunk.color]];">&nbsp;</div></td>
                        <td><label for="bloodline_chunk_markup">Chunks</label></td>
                    </tr>
                    <tr>
                        <td><input id="bloodline_snippet_markup" type="checkbox" name="markup[]" value="snippet" [[+snippet.ischecked]]/></td>
                        <td><div style="height:10px; width:10px; background-color: [[+snippet.color]];">&nbsp;</div></td>
                        <td><label for="bloodline_snippet_markup">Snippets</label></td>
                    </tr>
                    <tr>
                        <td><input id="bloodline_snippet_markup" type="checkbox" name="markup[]" value="link" [[+link.ischecked]]/></td>
                        <td><div style="height:10px; width:10px; background-color: [[+snippet.color]];">&nbsp;</div></td>
                        <td><label for="bloodline_snippet_markup">Links</label></td>
                    </tr>
                    <tr>
                        <td><input id="bloodline_lexicon_markup" type="checkbox" name="markup[]" value="lexicon" [[+lexicon.ischecked]]/></td>
                        <td><div style="height:10px; width:10px; background-color: [[+lexicon.color]];">&nbsp;</div></td>
                        <td><label for="bloodline_lexicon_markup">Lexicon</label></td>
                    </tr>
                    <tr>
                        <td><input id="bloodline_docvar_markup" type="checkbox" name="markup[]" value="docvar" [[+docvar.ischecked]]/></td>
                        <td><div style="height:10px; width:10px; background-color: [[+docvar.color]];">&nbsp;</div></td>
                        <td><label for="bloodline_docvar_markup">Docvars</label></td>
                    </tr>
                    <tr>
                        <td><input id="bloodline_tv_markup" type="checkbox" name="markup[]" value="tv" [[+tv.ischecked]]/></td>
                        <td><div style="height:10px; width:10px; background-color: [[+tv.color]];">&nbsp;</div></td>
                        <td><label for="bloodline_tv_markup">TVs</label></td>
                    </tr>
                     <tr>
                        <td><input id="bloodline_setting_markup" type="checkbox" name="markup[]" value="setting" [[+setting.ischecked]]/></td>
                        <td><div style="height:10px; width:10px; background-color: [[+setting.color]];">&nbsp;</div></td>
                        <td><label for="bloodline_setting_markup">Settings</label></td>
                    </tr>
                    <tr>
                        <td><input id="bloodline_placeholder_markup" type="checkbox" name="markup[]" value="placeholder" [[+placeholder.ischecked]]/></td>
                        <td><div style="height:10px; width:10px; background-color: [[+placeholder.color]];">&nbsp;</div></td>
                        <td><label for="bloodline_placeholder_markup">Placeholders</label></td>
                    </tr>
                </tbody>
            </table>
                       <div class="bloodline-btn-holder">
                            <input type="submit" class="bloodline-submit" value="Refresh" />
                       </div>         
               
            </div>
        </form>
        
        <div id="bloodline_footer"><a href="http://craftsmancoding.com/">&copy; 2013  Craftsman Coding</a></div>
    </div>
</div>
</div>
