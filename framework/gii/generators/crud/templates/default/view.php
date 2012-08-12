<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
if (is_array($this->tableSchema->primaryKey)){
	/* for composite primary keys, id is separated by the "|" character */
	$strFields = '';
	foreach($this->tableSchema->primaryKey as $nameField){
		$strFields .= '$model->'.$nameField.'.\'|\'.';
	}
	if ($strFields){
		$strFields = substr($strFields, 0, -5);
	}
}else{
	$strFields = '$model->'.$this->tableSchema->primaryKey;
}
?>
<?php echo "<?php\n"; ?>
/* @var $this <?php echo $this->getControllerClass(); ?> */
/* @var $model <?php echo $this->getModelClass(); ?> */

<?php
$nameColumn=$this->guessNameColumn($this->tableSchema->columns);
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\$this->breadcrumbs=array(
	'$label'=>array('index'),
	\$model->{$nameColumn},
);\n";
?>

$this->menu=array(
	array('label'=>'List <?php echo $this->modelClass; ?>', 'url'=>array('index')),
	array('label'=>'Create <?php echo $this->modelClass; ?>', 'url'=>array('create')),
	array('label'=>'Update <?php echo $this->modelClass; ?>', 'url'=>array('update', 'id'=><?php echo $strFields; ?>)),
	array('label'=>'Delete <?php echo $this->modelClass; ?>', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=><?php echo $strFields; ?>),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage <?php echo $this->modelClass; ?>', 'url'=>array('admin')),
);
?>

<h1>View <?php echo $this->modelClass." # <?php echo $strFields; ?>"; ?></h1>

<?php echo "<?php"; ?> $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
<?php
foreach($this->tableSchema->columns as $column)
	echo "\t\t'".$column->name."',\n";
?>
	),
)); ?>
