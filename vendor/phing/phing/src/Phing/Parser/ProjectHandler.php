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

use Phing\Io\File;
use Phing\Util\StringHelper;

/**
 * Handler class for the <project> XML element This class handles all elements
 * under the <project> element.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright (c) 2001,2002 THYRELL. All rights reserved
 */
class ProjectHandler extends AbstractHandler
{
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
     * Constructs a new ProjectHandler.
     *
     * @param ExpatParser         $parser        the ExpatParser object
     * @param AbstractHandler     $parentHandler the parent handler that invoked this handler
     * @param ProjectConfigurator $configurator  the ProjectConfigurator object
     */
    public function __construct(
        ExpatParser $parser,
        AbstractHandler $parentHandler,
        ProjectConfigurator $configurator,
        XmlContext $context
    ) {
        parent::__construct($parser, $parentHandler);

        $this->configurator = $configurator;
        $this->context = $context;
    }

    /**
     * Executes initialization actions required to setup the project. Usually
     * this method handles the attributes of a tag.
     *
     * @param string $tag   the tag that comes in
     * @param array  $attrs attributes the tag carries
     *
     * @throws ExpatParseException if attributes are incomplete or invalid
     */
    public function init($tag, $attrs)
    {
        $def = null;
        $name = null;
        $id = null;
        $desc = null;
        $baseDir = null;
        $ver = null;
        $strict = null;

        // some shorthands
        $project = $this->configurator->project;
        $buildFileParent = $this->configurator->buildFileParent;

        foreach ($attrs as $key => $value) {
            if ('default' === $key) {
                $def = $value;
            } elseif ('name' === $key) {
                $name = $value;
            } elseif ('id' === $key) {
                $id = $value;
            } elseif ('basedir' === $key) {
                $baseDir = $value;
            } elseif ('description' === $key) {
                $desc = $value;
            } elseif ('phingVersion' === $key) {
                $ver = $value;
            } elseif ('strict' === $key) {
                $strict = $value;
            } else {
                throw new ExpatParseException("Unexpected attribute '{$key}'");
            }
        }
        // these things get done no matter what
        if (null == $name) {
            $name = basename($this->configurator->getBuildFile());
        }

        $canonicalName = self::canonicalName($name);
        $this->configurator->setCurrentProjectName($canonicalName);
        $path = (string) $this->configurator->getBuildFile();
        $project->setUserProperty("phing.file.{$canonicalName}", $path);
        $project->setUserProperty("phing.dir.{$canonicalName}", dirname($path));

        if ($this->configurator->isIgnoringProjectTag()) {
            return;
        }

        if (null === $def) {
            throw new ExpatParseException(
                'The default attribute of project is required'
            );
        }

        $project->setDefaultTarget($def);

        if (null !== $name) {
            $project->setName($name);
            $project->addReference($name, $project);
        }

        if (null !== $id) {
            $project->addReference($id, $project);
        }

        if (null !== $desc) {
            $project->setDescription($desc);
        }

        if (null !== $ver) {
            $project->setPhingVersion($ver);
        }

        if (null !== $strict) {
            $project->setStrictMode(StringHelper::booleanValue($strict));
        }

        if (null !== $project->getProperty('project.basedir')) {
            $project->setBasedir($project->getProperty('project.basedir'));
        } else {
            if (null === $baseDir) {
                $project->setBasedir($buildFileParent->getAbsolutePath());
            } else {
                // check whether the user has specified an absolute path
                $f = new File($baseDir);
                if ($f->isAbsolute()) {
                    $project->setBasedir($baseDir);
                } else {
                    $project->setBaseDir($project->resolveFile($baseDir, $buildFileParent));
                }
            }
        }

        $project->addTarget('', $this->context->getImplicitTarget());
    }

    /**
     * Handles start elements within the <project> tag by creating and
     * calling the required handlers for the detected element.
     *
     * @param string $name  the tag that comes in
     * @param array  $attrs attributes the tag carries
     *
     * @throws ExpatParseException if a unxepected element occurs
     */
    public function startElement($name, $attrs)
    {
        $project = $this->configurator->project;
        $types = $project->getDataTypeDefinitions();

        if ('target' === $name || 'extension-point' === $name) {
            $tf = new TargetHandler($this->parser, $this, $this->configurator, $this->context);
            $tf->init($name, $attrs);
        } else {
            $tf = new ElementHandler(
                $this->parser,
                $this,
                $this->configurator,
                null,
                null,
                $this->context->getImplicitTarget()
            );
            $tf->init($name, $attrs);
        }
    }

    /**
     * @param $name
     */
    public static function canonicalName($name)
    {
        return preg_replace('/\W/', '_', strtolower($name));
    }
}
