模型-视图-控制器 (MVC)
===========================

Yii 使用了 Web 开发中广泛采用的模型-视图-控制器（MVC）设计模式。
MVC的目标是将业务逻辑从用户界面的考虑中分离，这样开发者就可以更容易地改变每一部分而不会影响其他。
在 MVC中，模型代表信息（数据）和业务规则；视图包含了用户界面元素，例如文本，表单等；
控制器则管理模型和视图中的通信。

除了 MVC, Yii 还引入了一个前端控制器，叫做 应用，它表示请求处理的执行上下文。
应用处理用户的请求并将其分派到一个合适的控制器以继续处理。

下面的示意图展示了 Yii 应用的静态结构：

![Static structure of Yii application](structure.png)


一个典型的工作流
------------------
下图展示了一个 Yii 应用在处理用户请求时典型的工作流。

![A typical workflow of Yii application](flow.png)

   1. 用户发出了访问 URL `http://www.example.com/index.php?r=post/show&id=1` 的请求，
Web 服务器通过执行入口脚本 `index.php` 处理此请求。
   2. 入口脚本创建了一个 [应用](/doc/guide/basics.application) 实例并执行。
   3. 应用从一个叫做 `request` 的 [应用组件](/doc/guide/basics.application#application-component)
中获得了用户请求的详细信息。
   4. 应用在一个名叫 `urlManager` 的应用组件的帮助下，决定请求的 [控制器](/doc/guide/basics.controller)
和 [动作](/doc/guide/basics.controller#action) 。在这个例子中，控制器是 `post`，它代表  `PostController` 类；
动作是 `show` ，其实际含义由控制器决定。
   5. 应用创建了一个所请求控制器的实例以进一步处理用户请求。控制器决定了动作
`show` 指向控制器类中的一个名为 `actionShow` 的方法。然后它创建并持行了与动作关联的过滤器（例如访问控制，基准测试）。
如果过滤器允许，动作将被执行。
   6. 动作从数据库中读取一个 ID 为 `1` 的 `Post` [模型](/doc/guide/basics.model)。
   7. 动作通过 `Post` 模型渲染一个名为 `show` 的 [视图](/doc/guide/basics.view)。
   8. 视图读取并显示 `Post` 模型的属性。
   9. 视图执行一些 [小物件](/doc/guide/basics.view#widget)。
   10. 视图的渲染结果被插入一个 [布局](/doc/guide/basics.view#layout)。
   11. 动作完成视图渲染并将其呈现给用户。


<div class="revision">$Id: basics.mvc.txt 1622 2009-12-26 20:56:05Z qiang.xue $</div>