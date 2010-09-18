<div class="sourceCode">
<b>Source Code:</b> <?php echo $this->renderSourceLink($object->sourcePath,$object->startLine); ?> (<b><a href="#" class="show">show</a></b>)
<pre class="code"><?php echo $this->highlight($object->getSourceCode()); ?></pre>
</div>