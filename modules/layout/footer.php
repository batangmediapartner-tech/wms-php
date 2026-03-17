<?php
if(!isset($GLOBALS['__footer_html'])){

$GLOBALS['__footer_html'] = '
<style>
.wms-footer{
    position: fixed;
    bottom: 0;
    left: 240px;
    width: calc(100% - 240px);
    height: 45px;
    background: #1b2a41;
    color: #ffffff;
    font-size: 13px;
    padding: 0 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-sizing: border-box;
}

body{
    padding-bottom:50px;
}

@media screen and (max-width:768px){
    .wms-footer{
        left:0;
        width:100%;
    }
}
</style>

<div class="wms-footer">
    <div class="footer-left">
        © 2026 WMS KBR By Why_20
    </div>

    <div class="footer-right">
        KBR Warehouse Management System
    </div>
</div>

';

register_shutdown_function(function(){
    echo $GLOBALS['__footer_html'];
});

}
?>