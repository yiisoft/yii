Creating User Menu Portlet
==========================

Based on the requirements analysis, we need three different portlets: the "user menu" portlet, the "tag cloud" portlet and the "recent comments" portlet. We will develop these portlets by extending the [CPortlet] widget provided by Yii.

In this section, we will develop our first concrete portlet - the user menu portlet which displays a list of menu items that are only available to authenticated users. The menu contains four items:

 * Approve Comments: a hyperlink that leads to a list of comments pending approval;
 * Create New Post: a hyperlink that leads to the post creation page;
 * Manage Posts: a hyperlink that leads to the post management page;
 * Logout: a link button that would log out the current user.


Creating `UserMenu` Class
-------------------------

We create the `UserMenu` class to represent the logic part of the user menu portlet. The class is saved in the file `/wwwroot/blog/protected/components/UserMenu.php` which has the following content:

~~~
[php]
Yii::import('zii.widgets.CPortlet');

class UserMenu extends CPortlet
{
	public function init()
	{
		$this->title=CHtml::encode(Yii::app()->user->name);
		parent::init();
	}

	protected function renderContent()
	{
		$this->render('userMenu');
	}
}
~~~

The `UserMenu` class extends from the `CPortlet` class from the `zii` library. It overrides both the `init()` method and the `renderContent()` method of `CPortlet`. The former sets the portlet title to be the name of the current user; the latter generates the portlet body content by rendering a view named `userMenu`.

> Tip: Notice that we have to explicitly include the `CPortlet` class by calling `Yii::import()` before we refer to it the first time. This is because `CPortlet` is part of the `zii` project -- the official extension library for Yii. For performance consideration, classes in this project are not listed as core classes. Therefore, we have to import it before we use it the first time.


Creating `userMenu` View
------------------------

Next, we create the `userMenu` view which is saved in the file `/wwwroot/blog/protected/components/views/userMenu.php`:

~~~
[php]
<ul>
	<li><?php echo CHtml::link('Create New Post',array('post/create')); ?></li>
	<li><?php echo CHtml::link('Manage Posts',array('post/admin')); ?></li>
	<li><?php echo CHtml::link('Approve Comments',array('comment/index'))
		. ' (' . Comment::model()->pendingCommentCount . ')'; ?></li>
	<li><?php echo CHtml::link('Logout',array('site/logout')); ?></li>
</ul>
~~~

> Info: By default, view files for a widget should be placed under the `views` sub-directory of the directory containing the widget class file. The file name must be the same as the view name.


Using `UserMenu` Portlet
------------------------

It is time for us to make use of our newly completed `UserMenu` portlet. We modify the layout view file `/wwwroot/blog/protected/views/layouts/column2.php` as follows:

~~~
[php]
......
<div id="sidebar">
	<?php if(!Yii::app()->user->isGuest) $this->widget('UserMenu'); ?>
</div>
......
~~~

In the above, we call the `widget()` method to generate and execute an instance of the `UserMenu` class. Because the portlet should only be displayed to authenticated users, we only call `widget()` when the `isGuest` property of the current user is false (meaning the user is authenticated).


Testing `UserMenu` Portlet
--------------------------

Let's test what we have so far.

 1. Open a browser window and enter the URL `http://www.example.com/blog/index.php`. Verify that there is nothing displayed in the side bar section of the page.
 2. Click on the `Login` hyperlink and fill out the login form to login. If successful, verify that the `UserMenu` portlet appears in the side bar and the portlet has the username as its title.
 3. Click on the 'Logout' hyperlink in the `UserMenu` portlet. Verify that the logout action is successful and the `UserMenu` portlet disappears.


Summary
-------

What we have created is a portlet that is highly reusable. We can easily reuse it in a different project with little or no modification. Moreover, the design of this portlet follows closely the philosophy that logic and presentation should be separated. While we did not point this out in the previous sections, such practice is used nearly everywhere in a typical Yii application.

<div class="revision">$Id$</div>