<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php
echo "<?php\n";
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\$this->breadcrumbs=array(
	'$label'=>array('index'),
	".$this->generateNameValue()."=>array('view','id'=>".$this->generatePrimaryKeyParam()."),
	'Update',
);\n";
?>

$this->menu=array(
	array('label'=>'List <?php echo $this->modelClass; ?>', 'url'=>array('index')),
	array('label'=>'Create <?php echo $this->modelClass; ?>', 'url'=>array('create')),
	array('label'=>'View <?php echo $this->modelClass; ?>', 'url'=>array('view', 'id'=><?php echo $this->generatePrimaryKeyParam(); ?>)),
	array('label'=>'Manage <?php echo $this->modelClass; ?>', 'url'=>array('admin')),
);
?>

<h1>Update <?php echo $this->modelClass." <?php echo ".$this->generatePrimaryKeyValue()."; ?>"; ?></h1>

<?php echo "<?php echo \$this->renderPartial('_form', array('model'=>\$model)); ?>"; ?>
