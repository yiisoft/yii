<?php
#
# Markdown Extra  -  A text-to-HTML conversion tool for web writers
#
# PHP Markdown Extra
# Copyright (c) 2004-2013 Michel Fortin  
# <http://michelf.com/projects/php-markdown/>
#
# Original Markdown
# Copyright (c) 2004-2006 John Gruber  
# <http://daringfireball.net/projects/markdown/>
#
# WARNING: This file differs from the original by absence of namespaces to provide support for earlier versions of PHP

# Just force Michelf/Markdown.php to load. This is needed to load
# the temporary implementation class. See below for details.
Markdown::MARKDOWNLIB_VERSION;

#
# Markdown Extra Parser Class
#
# Note: Currently the implementation resides in the temporary class
# \Michelf\MarkdownExtra_TmpImpl (in the same file as \Michelf\Markdown).
# This makes it easier to propagate the changes between the three different
# packaging styles of PHP Markdown. Once this issue is resolved, the
# _MarkdownExtra_TmpImpl will disappear and this one will contain the code.
#

class MarkdownExtra extends _MarkdownExtra_TmpImpl {

	### Parser Implementation ###

	# Temporarily, the implemenation is in the _MarkdownExtra_TmpImpl class.
	# See note above.

}

