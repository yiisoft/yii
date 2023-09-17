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
use Phing\ExtensionPoint;
use Phing\Project;
use Phing\Target;
use Phing\Util\StringHelper;

/**
 * The target handler class.
 *
 * This class handles the occurrence of a <target> tag and it's possible
 * nested tags (datatypes and tasks).
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 */
class TargetHandler extends AbstractHandler
{
    /**
     * Reference to the target object that represents the currently parsed
     * target.
     *
     * @var Target the target instance
     */
    private $target;

    /**
     * The phing project configurator object.
     *
     * @var ProjectConfigurator
     */
    private $configurator;

    /**
     * @var XmlContext
     */
    private $context;

    /**
     * Constructs a new TargetHandler.
     *
     * @internal param the $object ExpatParser object
     * @internal param the $object parent handler that invoked this handler
     * @internal param the $object ProjectConfigurator object
     */
    public function __construct(
        AbstractSAXParser $parser,
        AbstractHandler $parentHandler,
        ProjectConfigurator $configurator,
        XmlContext $context
    ) {
        parent::__construct($parser, $parentHandler);
        $this->configurator = $configurator;
        $this->context = $context;
    }

    /**
     * Executes initialization actions required to setup the data structures
     * related to the tag.
     * <p>
     * This includes:
     * <ul>
     * <li>creation of the target object</li>
     * <li>calling the setters for attributes</li>
     * <li>adding the target to the project</li>
     * <li>adding a reference to the target (if id attribute is given)</li>
     * </ul>.
     *
     * @param $tag
     * @param $attrs
     *
     * @throws BuildException
     * @throws ExpatParseException
     *
     * @internal param the $string tag that comes in
     * @internal param attributes $array the tag carries
     */
    public function init($tag, $attrs)
    {
        $name = null;
        $depends = '';
        $extensionPoint = null; //'fail';
        $extensionPointMissing = null;
        $ifCond = null;
        $unlessCond = null;
        $id = null;
        $description = null;
        $isHidden = false;
        $logskipped = false;

        foreach ($attrs as $key => $value) {
            switch ($key) {
                case 'name':
                    $name = (string) $value;

                    break;

                case 'depends':
                    $depends = (string) $value;

                    break;

                case 'if':
                    $ifCond = (string) $value;

                    break;

                case 'unless':
                    $unlessCond = (string) $value;

                    break;

                case 'id':
                    $id = (string) $value;

                    break;

                case 'hidden':
                    $isHidden = StringHelper::booleanValue($value);

                    break;

                case 'description':
                    $description = (string) $value;

                    break;

                case 'logskipped':
                    $logskipped = $value;

                    break;

                case 'extensionof':
                    $extensionPoint = $value;

                    break;

                case 'onmissingextensionpoint':
                    if (!in_array($value, ['fail', 'warn', 'ignore'], true)) {
                        throw new BuildException('Invalid onMissingExtensionPoint ' . $value);
                    }
                    $extensionPointMissing = $value;

                    break;

                default:
                    throw new ExpatParseException("Unexpected attribute '{$key}'", $this->parser->getLocation());
            }
        }

        if (null === $name) {
            throw new ExpatParseException(
                'target element appears without a name attribute',
                $this->parser->getLocation()
            );
        }

        // shorthand
        $project = $this->configurator->project;

        // check to see if this target is a dup within the same file
        if (isset($this->context->getCurrentTargets()[$name])) {
            throw new BuildException(
                "Duplicate target: {$name}",
                $this->parser->getLocation()
            );
        }

        $this->target = 'target' === $tag ? new Target() : new ExtensionPoint();
        $this->target->setProject($project);
        $this->target->setLocation($this->parser->getLocation());
        $this->target->setHidden($isHidden);
        $this->target->setIf($ifCond);
        $this->target->setUnless($unlessCond);
        $this->target->setDescription($description);
        $this->target->setLogSkipped(StringHelper::booleanValue($logskipped));
        // take care of dependencies
        if ('' !== $depends) {
            $this->target->setDepends($depends);
        }

        // check to see if target with same name is already defined
        $projectTargets = $project->getTargets();
        if (isset($projectTargets[$name])) {
            if (
                $this->configurator->isIgnoringProjectTag()
                && null != $this->configurator->getCurrentProjectName()
                && 0 != strlen($this->configurator->getCurrentProjectName())
            ) {
                // In an impored file (and not completely
                // ignoring the project tag)
                $newName = $this->configurator->getCurrentProjectName() . '.' . $name;
                $project->log(
                    'Already defined in main or a previous import, ' .
                    "define {$name} as {$newName}",
                    Project::MSG_VERBOSE
                );
                $name = $newName;
            } else {
                $project->log(
                    'Already defined in main or a previous import, ' .
                    "ignore {$name}",
                    Project::MSG_VERBOSE
                );
                $name = null;
            }
        }

        if (null !== $name) {
            $this->target->setName($name);
            $project->addOrReplaceTarget($name, $this->target);
            if (null !== $id && '' !== $id) {
                $project->addReference($id, $this->target);
            }
        }

        if (null !== $extensionPointMissing && null === $extensionPoint) {
            throw new BuildException(
                'onMissingExtensionPoint attribute cannot ' .
                'be specified unless extensionOf is specified',
                $this->target->getLocation()
            );
        }
        if (null !== $extensionPoint) {
            foreach (Target::parseDepends($extensionPoint, $name, 'extensionof') as $extPointName) {
                if (null === $extensionPointMissing) {
                    $extensionPointMissing = 'fail';
                }
                $this->context->addExtensionPoint([
                    $extPointName,
                    $this->target->getName(),
                    $extensionPointMissing,
                    null,
                ]);
            }
        }
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
        $tmp = new ElementHandler($this->parser, $this, $this->configurator, null, null, $this->target);
        $tmp->init($name, $attrs);
    }

    /**
     * Checks if this target has dependencies and/or nested tasks.
     * If the target has neither, show a warning.
     */
    protected function finished()
    {
        if (!$this->target instanceof ExtensionPoint && !count($this->target->getDependencies()) && !count($this->target->getTasks())) {
            $this->configurator->project->log(
                "Warning: target '" . $this->target->getName() .
                "' has no tasks or dependencies",
                Project::MSG_WARN
            );
        }
    }
}
