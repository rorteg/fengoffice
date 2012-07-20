<?php
$total = 5 ;
$genid = gen_id();

$workspaces =  Workspaces::getWorkspaces(10);
$data_ws = array();
foreach ($workspaces as $ws){
    if(count($data_ws) < $total){
        $data_ws[] = $ws;
    }    
}
include_once 'template.php';