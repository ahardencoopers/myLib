<?php
function pasarValores(&$stmtQuery, $formatoValores, 
$arrValores0
, 
$arrValores1
)
{
mysqli_stmt_bind_param($stmtQuery, $formatoValores, 
$arrValores0
, 
$arrValores1
);
}
?>
