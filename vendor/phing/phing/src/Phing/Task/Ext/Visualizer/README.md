# [READ-ONLY] task-visualizer

This is a read-only split of https://github.com/phingofficial/phing/tree/main/src/Phing/Task/Ext/Visualizer.

Please [report issues](https://github.com/phingofficial/phing/issues) and
[send Pull Requests](https://github.com/phingofficial/phing/pulls)
in the [main Phing repository](https://github.com/phingofficial/phing).

## VisualizerTask

**VisualizerTask creates diagrams using buildfiles, these diagrams represents calls and depends among targets.**

![buildfile](build.png)

[![Latest Stable Version](https://poser.pugx.org/phing/task-visualizer/v)](//packagist.org/packages/phing/task-visualizer)
[![Total Downloads](https://poser.pugx.org/phing/task-visualizer/downloads)](//packagist.org/packages/phing/task-visualizer)
[![composer.lock](https://poser.pugx.org/phing/task-visualizer/composerlock)](//packagist.org/packages/phing/task-visualizer)
[![.gitattributes](https://poser.pugx.org/phing/task-visualizer/gitattributes)](//packagist.org/packages/phing/task-visualizer)
[![License](https://poser.pugx.org/phing/task-visualizer/license)](//packagist.org/packages/phing/task-visualizer)

## Documentation

- [VisualizerTask documentation](https://www.phing.info/guide/chunkhtml/VisualizerTask.html)

## Examples

![demo](resources/examples/demo.png)
![ucenter](resources/examples/ucenter.png)
![demo](resources/examples/edu-resource-center.png)
![demo](resources/examples/enom-pro.png)
![demo](resources/examples/bitpay-magento.png)

## Requirements

<dl>
<dt>SimpleXML extension</dt>
<dd><code>apt install php7.3-xml</code> <em># adapt acording to your PHP version</em></dd>
<dt>XSL extension</dt>
<dd><code>apt install php7.3-xsl</code> <em># adapt acording to your PHP version</em></dd>
<dt>Guzzle</dt>
<dd><code>composer require guzzlehttp/guzzle</code></dd>
</dl>

## Contribute

If you liked this project, ⭐ star it on [GitHub](https://github.com/phingofficial/task-visualizer).

## License

This project is under the [GNU LGPL license](LICENSE.md).
