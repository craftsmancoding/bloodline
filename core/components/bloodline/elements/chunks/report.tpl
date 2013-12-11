<style>
    body {
        position: relative;
        margin: 0px;
    }
    #bloodline_report a,
    input[type="submit"],
    #toggle-bloodline {
       display: inline-block;
       font-size: 12px;
        padding: 1px 10px;
        margin-bottom: 0;
        font-weight: bold;
        line-height: 1.428571429;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        cursor: pointer;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;
        color: #000;
        background: #fff;
        border-color: #ddd;
        text-decoration: none;
    }
    #bloodline_report a:hover,
    input[type="submit"]:hover,
    #toggle-bloodline:hover {
        background: #bfbfbf;
    }



    #bloodline_report ul li {
        margin-bottom: 5px;
    }
    .bloodline_h2,
    .bloodline_h3 {
        padding-bottom: 10px;
        margin-bottom: 10px;
        border-bottom: 1px solid #ddd;
    }
    .bloodline-container {
         width: 350px;
         position: absolute;
         top: 0;
         right: 0;
    }

    #toggle-bloodline {
        position: absolute;
        right: 15px;
        top: 5px;
        z-index: 1000;
        text-indent: -9999px;
        padding: 4px 4px 0px !important;
    }
    #bloodline_report {
        width: 300px;
        border: 1px solid #999;
        color: #5d5d5d;
        font-size: 12px;
        font-family: sans-serif, arial;
        padding: 20px;
        background: #f7f7f7;
    }
    table {
        color: #5d5d5d;
        font-size: 12px;
        font-family: sans-serif, arial;
    }
    ul li a {
        color: #000 !important;
        background: none !important;
        border: none !important;
    }

    .bloodline_pageinfo ul,
    .bloodline_warnings ul,
    .bloodline_errors ul {
        padding-left: 0px;
    }

    .bloodline_pageinfo ul li,
    .bloodline_warnings ul li,
    .bloodline_errors ul li { 
        list-style: none;
        padding: 5px;
    }
    
    .bloodline_pageinfo ul li { 
        background: #d9edf7;
        border: 1px solid #bce8f1;
        color: #60708f;
    }

    .bloodline_warnings ul li { 
        background: #fcf8e3;
        border: 1px solid #faebcc;
        color: #8a6d6a;
    }
    
    .bloodline_errors ul li { 
        background: #f2dede;
        border: 1px solid #ebccd1;
        color: #a94442;
    }


    #bloodline_markup_checkboxes label {
        float: right;
        text-align: left;
        width: 250px;
    }

    .btn-holder {
        margin-top: 10px;
    }

    .line {
        border-top: 3px solid #000;
        display: block;
        height: 2px;
        width: 20px;
        margin-bottom: 1px;
    }
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script>
    //Pulse https://raw.github.com/jsoverson/jquery.pulse.js/master/jquery.pulse.min.js
    (function(t,n){"use strict";var e={pulses:1,interval:0,returnDelay:0,duration:500};t.fn.pulse=function(u,r,a){var i="destroy"===u;return"function"==typeof r&&(a=r,r={}),r=t.extend({},e,r),r.interval>=0||(r.interval=0),r.returnDelay>=0||(r.returnDelay=0),r.duration>=0||(r.duration=500),r.pulses>=-1||(r.pulses=1),"function"!=typeof a&&(a=function(){}),this.each(function(){function e(){return s.data("pulse").stop?void 0:r.pulses>-1&&++c>r.pulses?a.apply(s):(s.animate(u,{duration:r.duration/2,complete:function(){n.setTimeout(function(){s.animate(l,{duration:r.duration/2,complete:function(){n.setTimeout(e,r.interval)}})},r.returnDelay)}}),void 0)}var o,s=t(this),l={},p=s.data("pulse")||{};p.stop=i,s.data("pulse",p);for(o in u)u.hasOwnProperty(o)&&(l[o]=s.css(o));var c=0;e()})}})(jQuery,window,document);

    $(function(){
        $("#toggle-bloodline").click(function () {
            var effect = 'slide';
            var options = { direction: 'right' };
            var duration = 700;
            $('#bloodline_report').toggle(effect, options, duration);
        });
        // Heartbeat
        $('.bloodline_h1').pulse({color : 'red'}, { pulses : 2 });

    })
    
</script>

        
<div class="bloodline-container">
    <a id="toggle-bloodline">
        <div class="line">--</div>
        <div class="line">--</div>
        <div class="line">--</div>
    </a>
    <div id="bloodline_report">

        <h1 class="bloodline_h1">Bloodline</h1>
            <p>&copy; 2013 <a href="http://craftsmancoding.com/">Craftsman Coding</a></p>
            
        <h2 class="bloodline_h2 bloodline_info_header">Page Info</h2>
        <div class="bloodline_pageinfo">
            <ul>
            [[+bloodline.info]]
            </ul>
        </div>

        <h2 class="bloodline_h2 bloodline_warning_header">Warnings</h2>
        <div class="bloodline_warnings">
            <ul>
            [[+bloodline.warnings]]        
            </ul>
        </div>

        <h2 class="bloodline_h2 bloodline_error_header">Errors</h2>
        <div class="bloodline_errors">
            <ul>
            [[+bloodline.errors]]
            </ul>
        </div>    
        
        <h2 class="bloodline_h2">Tags</h2>
        <div class="bloodline_tags">
            <table>
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
        
        
        <!--h2 class="bloodline_h2">Markup</h2>
        <form id="bloodline_filter" action="[[+action_url]]" method="get">
            <input type="hidden" name="BLOODLINE" value="1" />
            <label for="bloodline_format" class="bloodline_label">Report Format</label>
            <a href="http://craftsmancoding.com/" class="bloodline_info">?</a>
            <select id="bloodline_format" name="format">
                <option value="html" [[+html.isselected]]>HTML</option>
                <option value="js" [[+js.isselected]]>Javascript</option>
                <option value="both" [[+both.isselected]]>Both</option>
            </select>
            
            
            <h3 class="bloodline_h3">Tags <a href="http://craftsmancoding.com/" class="bloodline_info">?</a></h3>
            
            <div id="bloodline_markup_checkboxes">
                <label for="bloodline_chunk_markup">Chunks</label> <input id="bloodline_chunk_markup" type="checkbox" name="markup[]" value="chunk" [[+chunk.ischecked]]/><br/>
                <label for="">Snippets</label> <input id="bloodline_chunk_markup" type="checkbox" name="markup[]" value="snippet" [[+snippet.ischecked]]/><br/>
                <label for="bloodline_snippet_markup">Links</label> <input id="bloodline_snippet_markup" type="checkbox" name="markup[]" value="link" [[+link.ischecked]]/><br/>
                <label for="bloodline_lexicon_markup">Lexicon</label> <input id="bloodline_lexicon_markup" type="checkbox" name="markup[]" value="lexicon" [[+lexicon.ischecked]]/><br/>
                <label for="bloodline_docvar_markup">Docvars</label> <input id="bloodline_docvar_markup" type="checkbox" name="markup[]" value="docvar" [[+docvar.ischecked]]/><br/>
                <label for="bloodline_tv_markup">TVs</label> <input id="bloodline_tv_markup" type="checkbox" name="markup[]" value="tv" [[+tv.ischecked]]/><br/>
                <label for="bloodline_setting_markup">Settings</label> <input id="bloodline_setting_markup" type="checkbox" name="markup[]" value="setting" [[+setting.ischecked]]/><br/>
                <label for="bloodline_placeholder_markup">Placeholders</label> <input id="bloodline_placeholder_markup" type="checkbox" name="markup[]" value="placeholder" [[+placeholder.ischecked]]/><br/>
                       <div class="btn-holder">
                            <input type="submit" value="Refresh" />
                            <a href="[[+action_url]]">Clear</a>
                       </div>         
               
            </div>
        </form-->
        
        <div id="bloodline_footer"><a href="http://craftsmancoding.com/">Craftsman Coding</a></div>
    </div>
</div>