<h2>
	Welcome, <?php echo Yii::app()->user->name; ?>!
</h2>
<p>
This is the homepage of <em><?php echo Yii::app()->name; ?></em>. You may modify the following files to customize the conent of this page:
</p>
<dl>
	<dt><?php echo Yii::app()->controllerPath . DIRECTORY_SEPARATOR . 'SiteController.php'; ?></dt>
	<dd>This file contains the <tt>SiteController</tt> class which is
	the default application controller. Its default <tt>index</tt> action
	renders the content of the following two files.
	</dd>
	<dt><?php echo __FILE__; ?></dt>
	<dd>This is the view file that contains the body content of this page.</dd>
	<dt><?php echo Yii::app()->layoutPath . DIRECTORY_SEPARATOR . 'main.php'; ?></dt>
	<dd>This is the layout file that contains common presentation (such as header, footer)	shared by all view files.</dd>
</dl>

<h3>What's Next</h3>
<ul>
	<li>Implement new actions in <tt>SiteController</tt>, and create corresponding views	under <?php echo Yii::app()->viewPath . DIRECTORY_SEPARATOR . 'site'; ?></li>
	<li>Create new controllers and actions manually or using the <tt>yiic</tt> tool.
	<li>If your Web application should be driven by database, do the following:
		<ul>
			<li>Set up database connection by configuring the <code>db</code> component in the application configuration
			<tt><?php echo Yii::app()->basePath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR .'main.php'; ?></tt></li>
			<li>Create model classes under the directory
			<tt><?php echo Yii::app()->basePath . DIRECTORY_SEPARATOR . 'models'; ?></tt></li>
			<li>Implement CRUD operations for a model class. For example, for the <tt>Post</tt> model class,
			you would create a <tt>PostController</tt> class together with <tt>create</tt>, <tt>read</tt>,
			<tt>update</tt> and <tt>delete</tt> actions.</li>
		</ul>
		Note, the <tt>yiic</tt> tool can automate the task of creating model classes and CRUD operations.
	</li>
</ul>

<p>
If you have problems in accomplishing any of the above tasks,
please read <a href="http://www.yiiframework.com/doc/">Yii documentation</a>
or visit <a href="http://www.yiiframework.com/forum/">Yii forum</a> for help.
</p>