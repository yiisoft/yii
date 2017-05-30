<?php
/** @var $this \site_demo\entry\dev\controllers\SiteController */
/** @var $arrDemo \lib\models\demo\domain\TbDemo[] */
/** @var $stat \lib\models\demo\domain\TbDemo */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>[<?php echo(__ENTRY_NAME__)?>] Site/Index</title>
    <style type="text/css">
    .frame-demos {}
    .frame-demos table {min-width:200px;border-collapse:collapse;}
    .frame-demos table th {padding:0 0.5em;border:1px solid #CCC;background-color:#EEE;}
    .frame-demos table td {text-align:center;border:1px solid #CCC;background-color:#FFF;}
    .frame-demos table .row-min td {background-color:#FFC;}
    .frame-demos table .row-max td {background-color:#FFC;}
    </style>
</head>
<body>
<div class="frame-demos">
    <div>Entry [<?php echo(__ENTRY_NAME__)?>] Route [<?php echo($this->getRoute())?>]</div>
    <div>&nbsp;</div>
    <table>
        <caption>[tb_demo]</caption>
        <tr>
            <th>#</th>
            <th>A</th>
            <th>B</th>
            <th>C</th>
            <th>avg</th>
        </tr>
        <tr class="row-min">
            <td>min</td>
            <td><?php echo($stat->getAlias('$$num_a_min'))?></td>
            <td><?php echo($stat->getAlias('$$num_b_min'))?></td>
            <td><?php echo($stat->getAlias('$$num_c_min'))?></td>
            <td><?php echo($stat->getAlias('$$min_avg'))?></td>
        </tr>
        <tr class="row-max">
            <td>max</td>
            <td><?php echo($stat->getAlias('$$num_a_max'))?></td>
            <td><?php echo($stat->getAlias('$$num_b_max'))?></td>
            <td><?php echo($stat->getAlias('$$num_c_max'))?></td>
            <td><?php echo($stat->getAlias('$$max_avg'))?></td>
        </tr>
        <?php foreach($arrDemo as $demo) { ?>
        <tr>
            <td><?php echo($demo->id)?></td>
            <td><?php echo($demo->num_a)?></td>
            <td><?php echo($demo->num_b)?></td>
            <td><?php echo($demo->num_c)?></td>
            <td><?php echo($demo->getAlias('$$avg'))?></td>
        </tr>
        <?php }?>
    </table>
</div>
</body>
</html>