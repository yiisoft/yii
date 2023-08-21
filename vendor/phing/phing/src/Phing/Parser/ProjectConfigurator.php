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

use Exception;
use Phing\Exception\BuildException;
use Phing\Exception\ExitStatusException;
use Phing\ExtensionPoint;
use Phing\IntrospectionHelper;
use Phing\Io\BufferedReader;
use Phing\Io\File;
use Phing\Io\FileReader;
use Phing\Io\IOException;
use Phing\Project;
use Phing\Target;
use Phing\Task;
use Phing\TypeAdapter;
use Phing\UnknownElement;

/**
 * The datatype handler class.
 *
 * This class handles the occurrence of registered datatype tags like
 * FileSet
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 */
class ProjectConfigurator
{
    public const PARSING_CONTEXT_REFERENCE = 'phing.parsing.context';

    /**
     * @var Project
     */
    public $project;
    public $locator;

    public $buildFile;
    public $buildFileParent;

    /**
     * Synthetic target that will be called at the end to the parse phase.
     */
    private $parseEndTarget;

    /**
     * Name of the current project.
     */
    private $currentProjectName;

    private $isParsing = true;

    /**
     * Indicates whether the project tag attributes are to be ignored
     * when processing a particular build file.
     */
    private $ignoreProjectTag = false;

    /**
     * Constructs a new ProjectConfigurator object
     * This constructor is private. Use a static call to
     * <code>configureProject</code> to configure a project.
     *
     * @param Project $project   the Project instance this configurator should use
     * @param File    $buildFile the buildfile object the parser should use
     *
     * @throws IOException
     * @throws \InvalidArgumentException
     */
    private function __construct(Project $project, File $buildFile)
    {
        $this->project = $project;
        $this->buildFile = new File($buildFile->getAbsolutePath());
        $this->buildFileParent = new File($this->buildFile->getParent());
        $this->parseEndTarget = new Target();
    }

    /**
     * Static call to ProjectConfigurator. Use this to configure a
     * project. Do not use the new operator.
     *
     * @param Project $project   the Project instance this configurator should use
     * @param File    $buildFile the buildfile object the parser should use
     *
     * @throws IOException
     * @throws BuildException
     * @throws \InvalidArgumentException
     */
    public static function configureProject(Project $project, File $buildFile): void
    {
        (new self($project, $buildFile))->parse();
    }

    /**
     * find out the build file.
     *
     * @return File the build file to which the xml context belongs
     */
    public function getBuildFile()
    {
        return $this->buildFile;
    }

    /**
     * find out the parent build file of this build file.
     *
     * @return File the parent build file of this build file
     */
    public function getBuildFileParent()
    {
        return $this->buildFileParent;
    }

    /**
     * find out the current project name.
     *
     * @return string current project name
     */
    public function getCurrentProjectName()
    {
        return $this->currentProjectName;
    }

    /**
     * set the name of the current project.
     *
     * @param string $name name of the current project
     */
    public function setCurrentProjectName($name)
    {
        $this->currentProjectName = $name;
    }

    /**
     * tells whether the project tag is being ignored.
     *
     * @return bool whether the project tag is being ignored
     */
    public function isIgnoringProjectTag()
    {
        return $this->ignoreProjectTag;
    }

    /**
     * sets the flag to ignore the project tag.
     *
     * @param bool $flag flag to ignore the project tag
     */
    public function setIgnoreProjectTag($flag)
    {
        $this->ignoreProjectTag = $flag;
    }

    /**
     * @return bool
     */
    public function isParsing()
    {
        return $this->isParsing;
    }

    /**
     * Delay execution of a task until after the current parse phase has
     * completed.
     *
     * @param Task $task Task to execute after parse
     */
    public function delayTaskUntilParseEnd($task)
    {
        $this->parseEndTarget->addTask($task);
    }

    /**
     * Configures an element and resolves eventually given properties.
     *
     * @param mixed   $target  element to configure
     * @param array   $attrs   element's attributes
     * @param Project $project project this element belongs to
     *
     * @throws BuildException
     * @throws Exception
     */
    public static function configure($target, $attrs, Project $project)
    {
        if ($target instanceof TypeAdapter) {
            $target = $target->getProxy();
        }

        // if the target is an UnknownElement, this means that the tag had not been registered
        // when the enclosing element (task, target, etc.) was configured.  It is possible, however,
        // that the tag was registered (e.g. using <taskdef>) after the original configuration.
        // ... so, try to load it again:
        if ($target instanceof UnknownElement) {
            $tryTarget = $project->createTask($target->getTaskType());
            if ($tryTarget) {
                $target = $tryTarget;
            }
        }

        $bean = get_class($target);
        $ih = IntrospectionHelper::getHelper($bean);

        foreach ($attrs as $key => $value) {
            if ('id' === $key) {
                continue;
                // throw new BuildException("Id must be set Extermnally");
            }
            if (!is_string($value) && method_exists($value, 'main')) {
                $value = $value->main();
            } else {
                $value = $project->replaceProperties($value);
            }

            try { // try to set the attribute
                $ih->setAttribute($project, $target, strtolower($key), $value);
            } catch (BuildException $be) {
                // id attribute must be set externally
                if ('id' !== $key) {
                    throw $be;
                }
            }
        }
    }

    /**
     * Configures the #CDATA of an element.
     *
     * @param Project $project the project this element belongs to
     * @param object  the element to configure
     * @param string $text   the element's #CDATA
     * @param mixed  $target
     */
    public static function addText($project, $target, $text = null)
    {
        if (null === $text || 0 === strlen(trim($text))) {
            return;
        }
        $ih = IntrospectionHelper::getHelper(get_class($target));
        $text = $project->replaceProperties($text);
        $ih->addText($project, $target, $text);
    }

    /**
     * Stores a configured child element into its parent object.
     *
     * @param object  the project this element belongs to
     * @param object  the parent element
     * @param object  the child element
     * @param string  the XML tagname
     * @param mixed $project
     * @param mixed $parent
     * @param mixed $child
     * @param mixed $tag
     */
    public static function storeChild($project, $parent, $child, $tag)
    {
        $ih = IntrospectionHelper::getHelper(get_class($parent));
        $ih->storeElement($project, $parent, $child, $tag);
    }

    /**
     * Scan Attributes for the id attribute and maybe add a reference to
     * project.
     *
     * @param object $target the element's object
     * @param array  $attr   the element's attributes
     */
    public function configureId($target, $attr)
    {
        if (isset($attr['id']) && null !== $attr['id']) {
            $this->project->addReference($attr['id'], $target);
        }
    }

    /**
     * Add location to build exception.
     *
     * @param BuildException $ex          the build exception, if the build exception
     *                                    does not include
     * @param Location       $newLocation the location of the calling task (may be null)
     *
     * @return BuildException a new build exception based in the build exception with
     *                        location set to newLocation. If the original exception
     *                        did not have a location, just return the build exception
     */
    public static function addLocationToBuildException(BuildException $ex, Location $newLocation)
    {
        if (null === $ex->getLocation() || null === $ex->getMessage()) {
            return $ex;
        }
        $errorMessage = sprintf(
            'The following error occurred while executing this line:%s%s %s%s',
            PHP_EOL,
            $ex->getLocation(),
            $ex->getMessage(),
            PHP_EOL
        );
        if ($ex instanceof ExitStatusException) {
            $exitStatus = $ex->getCode();
            if (null === $newLocation) {
                return new ExitStatusException($errorMessage, $exitStatus);
            }

            return new ExitStatusException($errorMessage, $exitStatus, $newLocation);
        }

        return new BuildException($errorMessage, $ex, $newLocation);
    }

    /**
     * Check extensionStack and inject all targets having extensionOf attributes
     * into extensionPoint.
     * <p>
     * This method allow you to defer injection and have a powerful control of
     * extensionPoint wiring.
     * </p>
     * <p>
     * This should be invoked by each concrete implementation of ProjectHelper
     * when the root "buildfile" and all imported/included buildfile are loaded.
     * </p>.
     *
     * @param Project $project The project containing the target. Must not be
     *                         <code>null</code>.
     *
     * @throws BuildException if OnMissingExtensionPoint.FAIL and
     *                        extensionPoint does not exist
     *
     * @see OnMissingExtensionPoint
     */
    public function resolveExtensionOfAttributes(Project $project, XmlContext $ctx)
    {
        /** @var XmlContext $ctx */
        foreach ($ctx->getExtensionPointStack() as [$extPointName, $targetName, $missingBehaviour, $prefixAndSep]) {
            // find the target we're extending
            $projectTargets = $project->getTargets();
            $extPoint = null;
            if (null === $prefixAndSep) {
                // no prefix - not from an imported/included build file
                $extPoint = $projectTargets[$extPointName] ?? null;
            } else {
                // we have a prefix, which means we came from an include/import

                // FIXME: here we handle no particular level of include. We try
                // the fully prefixed name, and then the non-prefixed name. But
                // there might be intermediate project in the import stack,
                // which prefix should be tested before testing the non-prefix
                // root name.

                $extPoint = $projectTargets[$prefixAndSep . $extPointName] ?? null;
                if (null === $extPoint) {
                    $extPoint = $projectTargets[$extPointName] ?? null;
                }
            }

            // make sure we found a point to extend on
            if (null === $extPoint) {
                $message = "can't add target " . $targetName
                    . ' to extension-point ' . $extPointName
                    . ' because the extension-point is unknown.';
                if ('fail' === $missingBehaviour) {
                    throw new BuildException($message);
                }
                if ('warn' === $missingBehaviour) {
                    $t = $projectTargets[$targetName];
                    $project->log('Warning: ' . $message, Project::MSG_WARN);
                }
            } else {
                if (!$extPoint instanceof ExtensionPoint) {
                    throw new BuildException('referenced target ' . $extPointName
                        . ' is not an extension-point');
                }
                $extPoint->addDependency($targetName);
            }
        }
    }

    /**
     * Creates the ExpatParser, sets root handler and kick off parsing
     * process.
     *
     * @throws BuildException if there is any kind of exception during
     *                        the parsing process
     */
    protected function parse()
    {
        try {
            // get parse context
            $ctx = $this->project->getReference(self::PARSING_CONTEXT_REFERENCE);
            if (null == $ctx) {
                // make a new context and register it with project
                $ctx = new XmlContext($this->project);
                $this->project->addReference(self::PARSING_CONTEXT_REFERENCE, $ctx);
            }

            //record this parse with context
            $ctx->addImport($this->buildFile);

            if (count($ctx->getImportStack()) > 1) {
                $currentImplicit = $ctx->getImplicitTarget();
                $currentTargets = $ctx->getCurrentTargets();

                $newCurrent = new Target();
                $newCurrent->setProject($this->project);
                $newCurrent->setName('');
                $ctx->setCurrentTargets([]);
                $ctx->setImplicitTarget($newCurrent);

                // this is an imported file
                // modify project tag parse behavior
                $this->setIgnoreProjectTag(true);
                $this->innerParse($ctx);
                $newCurrent->main();

                $ctx->setImplicitTarget($currentImplicit);
                $ctx->setCurrentTargets($currentTargets);
            } else {
                $ctx->setCurrentTargets([]);
                $this->innerParse($ctx);
                $ctx->getImplicitTarget()->main();
            }

            $this->resolveExtensionOfAttributes($this->project, $ctx);
        } catch (Exception $exc) {
            //throw new BuildException("Error reading project file", $exc);
            throw $exc;
        }
    }

    /**
     * @throws ExpatParseException
     */
    protected function innerParse(XmlContext $ctx)
    {
        // push action onto global stack
        $ctx->startConfigure($this);

        $reader = new BufferedReader(new FileReader($this->buildFile));
        $parser = new ExpatParser($reader);
        $parser->parserSetOption(XML_OPTION_CASE_FOLDING, 0);
        $parser->setHandler(new RootHandler($parser, $this, $ctx));
        $this->project->log('parsing buildfile ' . $this->buildFile->getName(), Project::MSG_VERBOSE);
        $parser->parse();
        $reader->close();

        // mark parse phase as completed
        $this->isParsing = false;
        // execute delayed tasks
        $this->parseEndTarget->main();
        // pop this action from the global stack
        $ctx->endConfigure();
    }
}
