<?php
/** @var string $javaScriptCode */

?>
<div id="wrapper" class="">
    <div id="sidebar" class="col-xs-4">
        <div id="menu">
            <div id="menu-wrapper">
                <ul id="menu-titles">
                    <li class="module selected" id="section_js">

                        <img src="" width="30" height="30" alt="icon">

                        <h4>Embed JS code in a web site page</h4><br>
                        <span>Render form inside your own domain with help JS code</span>
                    </li>
                    <li class="no-module disabled"></li>
                </ul>
            </div>
        </div>
        <div id="clearfix"></div>
    </div>
    <div id="content" class="col-xs-8">
        <div id="spopup" class="psection" style="display: block;">
            <div class="popup_code">
                <span class="title">Copy and paste this piece of code and place in your website</span>
                <div class="textarea-wrapper">
                    <textarea id="embed-code" readonly="readonly" style="resize: none; overflow: hidden; height: 324px;" title=""><?= is_string($javaScriptCode)?$javaScriptCode:'The code is absent!'; ?></textarea>
                </div>
                <span class="description">Simply paste this code into your HTML document where you want the launcher to appear, that’s it!</span>
                <br><br>
            </div>
        </div>
    </div>
</div>