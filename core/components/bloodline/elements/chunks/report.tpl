<style>

</style>
<div id="bloodline_report">
    <h1 class="bloodline_h1">Bloodline</h1>
    
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
            
        [[+bloodline.tags]]        
        </table>
    </div>
    
    
    <h2 class="bloodline_h2">Markup</h2>
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
                            
            <input type="submit" value="Refresh" />
            <a href="[[+action_url]]">Clear</a>
        </div>
    </form>
    
    <div id="bloodline_footer"><a href="http://craftsmancoding.com/">Craftsman Coding</a></div>
</div>