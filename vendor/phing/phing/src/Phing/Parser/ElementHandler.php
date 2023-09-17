<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

namespace Phing\Parser;

use Phing\Exception\BuildException;
use Phing\RuntimeConfigurable;
use Phing\Target;
use Phing\UnknownElement;

/**
 * The generic element handler class.
 *
 * This class handles the occurrence of runtime registered tags like
 * datatypes (fileset, patternset, etc) and it's possible nested tags. It
 * introspects the implementation of the class and sets up the data structures.
 *
 * @author    Michiel Rook <mrook@php.net>
 * @copyright 2001,2002 THYRELL. All rights reserved
 */
class ElementHandler extends AbstractHandler
{
    /**
     * Reference to the parent object that represents the parent tag
     * of this nested element.
     *
     * @var object
     */
    private $parent;

    /**
     * Reference to the child object that represents the child tag
     * of this nested element.
     *
     * @var UnknownElement
     */
    private $child;

    /**
     *  Reference to the parent wrapper object.
     *
     * @var RuntimeConfigurable
     */
    private $parentWrapper;

    /**
     *  Reference to the child wrapper object.
     *
     * @var RuntimeConfigurable
     */
    private $childWrapper;

    /**
     *  Reference to the related target object.
     *
     * @var Target the target instance
     */
    private $target;

    /**
     * @var ProjectConfigurator
     */
    private $configurator;

    /**
     *  Constructs a new NestedElement handler and sets up everything.
     *
     * @param AbstractSAXParser   $parser        the ExpatParser object
     * @param AbstractHandler     $parentHandler the parent handler that invoked this handler
     * @param ProjectConfigurator $configurator  the ProjectConfigurator object
     * @param UnknownElement      $parent        the parent object this element is contained in
     * @param RuntimeConfigurable $parentWrapper the parent wrapper object
     * @param Target              $target        the target object this task is contained in
     */
    public function __construct(
        AbstractSAXParser $parser,
        AbstractHandler $parentHandler,
        ProjectConfigurator $configurator,
        UnknownElement $parent = null,
        RuntimeConfigurable $parentWrapper = null,
        Target $target = null
    ) {
        parent::__construct($parser, $parentHandler);
        $this->configurator = $configurator;
        if (null != $parentWrapper) {
            $this->parent = $parentWrapper->getProxy();
        } else {
            $this->parent = $parent;
        }
        $this->parentWrapper = $parentWrapper;
        $this->target = $target;
    }

    /**
     * Executes initialization actions required to setup the data structures
     * related to the tag.
     * <p>
     * This includes:
     * <ul>
     * <li>creation of the nested element</li>
     * <li>calling the setters for attributes</li>
     * <li>adding the element to the container object</li>
     * <li>adding a reference to the element (if id attribute is given)</li>
     * </ul>.
     *
     * @param string $tag   the tag that comes in
     * @param array  $attrs attributes the tag carries
     *
     * @throws ExpatParseException if the setup process fails
     */
    public function init($tag, $attrs)
    {
        $configurator = $this->configurator;
        $project = $this->configurator->project;

        try {
            $this->child = new UnknownElement(strtolower($tag));
            $this->child->setTaskName($tag);
            $this->child->setTaskType($tag);
            $this->child->setProject($project);
            $this->child->setLocation($this->parser->getLocation());

            if (null !== $this->target) {
                $this->child->setOwningTarget($this->target);
            }

            if (null !== $this->parent) {
                $this->parent->addChild($this->child);
            } elseif (null !== $this->target) {
                $this->target->addTask($this->child);
            }

            $configurator->configureId($this->child, $attrs);

            $this->childWrapper = new RuntimeConfigurable($this->child, $tag);
            $this->childWrapper->setAttributes($attrs);

            if (null !== $this->parentWrapper) {
                $this->parentWrapper->addChild($this->childWrapper);
            }
        } catch (BuildException $exc) {
            throw new ExpatParseException(
                "Error initializing nested element <{$tag}>",
                $exc,
                $this->parser->getLocation()
            );
        }
    }

    /**
     * Handles character data.
     *
     * @param string $data the CDATA that comes in
     *
     * @throws ExpatParseException if the CDATA could not be set-up properly
     */
    public function characters($data)
    {
        $this->childWrapper->addText($data);
    }

    /**
     * Checks for nested tags within the current one. Creates and calls
     * handlers respectively.
     *
     * @param string $name  the tag that comes in
     * @param array  $attrs attributes the tag carries
     */
    public function startElement($name, $attrs)
    {
        $eh = new ElementHandler(
            $this->parser,
            $this,
            $this->configurator,
            $this->child,
            $this->childWrapper,
            $this->target
        );
        $eh->init($name, $attrs);
    }
}
