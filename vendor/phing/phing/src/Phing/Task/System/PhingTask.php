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

namespace Phing\Task\System;

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileOutputStream;
use Phing\Io\FileUtils;
use Phing\Io\OutputStream;
use Phing\Listener\DefaultLogger;
use Phing\Parser\ProjectConfigurator;
use Phing\Phing;
use Phing\Project;
use Phing\ProjectComponent;
use Phing\Target;
use Phing\Task;
use Phing\Type\Element\FileSetAware;
use Phing\Type\Reference;

/**
 * Task that invokes phing on another build file.
 *
 * Use this task, for example, if you have nested buildfiles in your project. Unlike
 * AntTask, PhingTask can even support filesets:
 *
 * <pre>
 *   <phing>
 *    <fileset dir="${srcdir}">
 *      <include name="** /build.xml" /> <!-- space added after ** is there because of PHP comment syntax -->
 *      <exclude name="build.xml" />
 *    </fileset>
 *   </phing>
 * </pre>
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class PhingTask extends Task
{
    use FileSetAware;

    /**
     * the target to call if any.
     *
     * @var Target
     */
    protected $newTarget;

    /**
     * the basedir where is executed the build file.
     *
     * @var File
     */
    private $dir;

    /**
     * build.xml (can be absolute) in this case dir will be ignored.
     */
    private $phingFile;

    /**
     * should we inherit properties from the parent ?
     */
    private $inheritAll = true;

    /**
     * should we inherit references from the parent ?
     */
    private $inheritRefs = false;

    /**
     * the properties to pass to the new project.
     */
    private $properties = [];

    /**
     * the references to pass to the new project.
     *
     * @var PhingReference[]
     */
    private $references = [];

    /**
     * The temporary project created to run the build file.
     *
     * @var Project
     */
    private $newProject;

    /**
     * Fail the build process when the called build fails?
     */
    private $haltOnFailure = false;

    /**
     * Whether the basedir of the new project should be the same one
     * as it would be when running the build file directly -
     * independent of dir and/or inheritAll settings.
     */
    private $useNativeBasedir = false;

    /**
     * @var OutputStream
     */
    private $out;

    /** @var string */
    private $output;

    /**
     * @var array
     */
    private $locals;

    public function __construct(Task $owner = null)
    {
        if (null !== $owner) {
            $this->bindToOwner($owner);
        }
        parent::__construct();
    }

    /**
     *  If true, abort the build process if there is a problem with or in the target build file.
     *  Defaults to false.
     *
     * @param bool $hof new value
     */
    public function setHaltOnFailure($hof)
    {
        $this->haltOnFailure = (bool) $hof;
    }

    /**
     * Whether the basedir of the new project should be the same one
     * as it would be when running the build file directly -
     * independent of dir and/or inheritAll settings.
     */
    public function setUseNativeBasedir(bool $b)
    {
        $this->useNativeBasedir = $b;
    }

    /**
     * Creates a Project instance for the project to call.
     */
    public function init()
    {
        $this->newProject = $this->getProject()->createSubProject();
        $tdf = $this->project->getTaskDefinitions();
        $this->newProject->addTaskDefinition('property', $tdf['property']);
    }

    /**
     * Main entry point for the task.
     */
    public function main()
    {
        // Call Phing on the file set with the attribute "phingfile"
        if (null !== $this->phingFile || null !== $this->dir) {
            $this->processFile();
        }

        // if no filesets are given stop here; else process filesets
        if (!empty($this->filesets)) {
            // preserve old settings
            $savedDir = $this->dir;
            $savedPhingFile = $this->phingFile;
            $savedTarget = $this->newTarget;

            // set no specific target for files in filesets
            // [HL] I'm commenting this out; I don't know why this should not be supported!
            // $this->newTarget = null;

            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($this->project);

                $fromDir = $fs->getDir($this->project);
                $srcFiles = $ds->getIncludedFiles();

                foreach ($srcFiles as $fname) {
                    $f = new File($ds->getbasedir(), $fname);
                    $f = $f->getAbsoluteFile();
                    $this->phingFile = $f->getAbsolutePath();
                    $this->dir = $f->getParentFile();
                    $this->processFile(); // run Phing!
                }
            }

            // side effect free programming ;-)
            $this->dir = $savedDir;
            $this->phingFile = $savedPhingFile;
            $this->newTarget = $savedTarget;

            // [HL] change back to correct dir
            if (null !== $this->dir) {
                chdir($this->dir->getAbsolutePath());
            }
        }

        // Remove any dangling references to help the GC
        foreach ($this->properties as $property) {
            $property->setFallback(null);
        }
    }

    /**
     * If true, pass all properties to the new phing project.
     * Defaults to true.
     *
     * @param bool $inheritAll
     */
    public function setInheritAll($inheritAll)
    {
        $this->inheritAll = (bool) $inheritAll;
    }

    /**
     * If true, pass all references to the new phing project.
     * Defaults to false.
     *
     * @param bool $inheritRefs
     */
    public function setInheritRefs($inheritRefs)
    {
        $this->inheritRefs = (bool) $inheritRefs;
    }

    /**
     * The directory to use as a base directory for the new phing project.
     * Defaults to the current project's basedir, unless inheritall
     * has been set to false, in which case it doesn't have a default
     * value. This will override the basedir setting of the called project.
     */
    public function setDir(File $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * The build file to use.
     * Defaults to "build.xml". This file is expected to be a filename relative
     * to the dir attribute given.
     */
    public function setPhingFile(string $file)
    {
        // it is a string and not a file to handle relative/absolute
        // otherwise a relative file will be resolved based on the current
        // basedir.
        $this->phingFile = $file;
    }

    /**
     * Alias function for setPhingfile.
     *
     * @param string $file
     */
    public function setBuildfile($file)
    {
        $this->setPhingFile($file);
    }

    /**
     * The target of the new Phing project to execute.
     * Defaults to the new project's default target.
     */
    public function setTarget(string $target)
    {
        if ('' === $target) {
            throw new BuildException('target attribute must not be empty');
        }

        $this->newTarget = $target;
    }

    /**
     * Set the filename to write the output to. This is relative to the value
     * of the dir attribute if it has been set or to the base directory of the
     * current project otherwise.
     *
     * @param string $outputFile the name of the file to which the output should go
     */
    public function setOutput(string $outputFile): void
    {
        $this->output = $outputFile;
    }

    /**
     * Property to pass to the new project.
     * The property is passed as a 'user property'.
     */
    public function createProperty()
    {
        $p = new PropertyTask();
        $p->setFallback($this->getNewProject());
        $p->setUserProperty(true);
        $p->setTaskName('property');
        $this->properties[] = $p;

        return $p;
    }

    /**
     * Reference element identifying a data type to carry
     * over to the new project.
     */
    public function addReference(PhingReference $ref)
    {
        $this->references[] = $ref;
    }

    /**
     * Get the (sub)-Project instance currently in use.
     */
    protected function getNewProject(): Project
    {
        if (null === $this->newProject) {
            $this->reinit();
        }

        return $this->newProject;
    }

    /**
     * Called in execute or createProperty if newProject is null.
     *
     * <p>This can happen if the same instance of this task is run
     * twice as newProject is set to null at the end of execute (to
     * save memory and help the GC).</p>
     *
     * <p>Sets all properties that have been defined as nested
     * property elements.</p>
     */
    private function reinit()
    {
        $this->init();

        $count = count($this->properties);
        for ($i = 0; $i < $count; ++$i) {
            /**
             * @var PropertyTask $p
             */
            $p = $this->properties[$i] ?? null;
            if (null !== $p) {
                /** @var PropertyTask $newP */
                $newP = $this->newProject->createTask('property');
                $newP->setName($p->getName());
                if (null !== $p->getValue()) {
                    $newP->setValue($p->getValue());
                }
                if (null !== $p->getFile()) {
                    $newP->setFile($p->getFile());
                }
                if (null !== $p->getPrefix()) {
                    $newP->setPrefix($p->getPrefix());
                }
                if (null !== $p->getRefid()) {
                    $newP->setRefid($p->getRefid());
                }
                if (null !== $p->getEnvironment()) {
                    $newP->setEnvironment($p->getEnvironment());
                }
                if (null !== $p->getUserProperty()) {
                    $newP->setUserProperty($p->getUserProperty());
                }
                $newP->setOverride($p->getOverride());
                $newP->setLogoutput($p->getLogoutput());
                $newP->setQuiet($p->getQuiet());

                $this->properties[$i] = $newP;
            }
        }
    }

    /**
     * Execute phing file.
     *
     * @throws BuildException
     */
    private function processFile(): void
    {
        $buildFailed = false;
        $buildFailedCause = null;
        $savedDir = $this->dir;
        $savedPhingFile = $this->phingFile;
        $savedTarget = $this->newTarget;

        $savedBasedirAbsPath = null; // this is used to save the basedir *if* we change it

        try {
            $this->getNewProject();

            $this->initializeProject();

            if (null !== $this->dir) {
                if (!$this->useNativeBasedir) {
                    $this->newProject->setBasedir($this->dir);
                    if (null !== $savedDir) { // has been set explicitly
                        $this->newProject->setInheritedProperty('project.basedir', $this->dir->getAbsolutePath());
                    }
                }
            } else {
                // Since we're not changing the basedir here (for file resolution),
                // we don't need to worry about any side-effects in this scanrio.
                $this->dir = $this->getProject()->getBasedir();
            }

            $this->overrideProperties();
            $this->phingFile = $this->phingFile ?? Phing::DEFAULT_BUILD_FILENAME;

            $fu = new FileUtils();
            $file = $fu->resolveFile($this->dir, $this->phingFile);
            $this->phingFile = $file->getAbsolutePath();
            $this->log('calling target(s) '
                . (empty($this->locals) ? '[default]' : implode(', ', $this->locals))
                . ' in build file ' . $this->phingFile, Project::MSG_VERBOSE);

            $this->newProject->setUserProperty('phing.file', $this->phingFile);

            if (empty($this->locals)) {
                $defaultTarget = $this->newProject->getDefaultTarget();
                if (!empty($defaultTarget)) {
                    $this->locals[] = $defaultTarget;
                }
            }

            $thisPhingFile = $this->getProject()->getProperty('phing.file');
            // Are we trying to call the target in which we are defined (or
            // the build file if this is a top level task)?
            if (
                null !== $thisPhingFile
                && null !== $this->getOwningTarget()
                && $thisPhingFile === $file->getPath()
                && '' === $this->getOwningTarget()->getName()
            ) {
                if ('phingcall' === $this->getTaskName()) {
                    throw new BuildException('phingcall must not be used at the top level.');
                }

                throw new BuildException(
                    sprintf(
                        '%s task at the top level must not invoke its own build file.',
                        $this->getTaskName()
                    )
                );
            }

            ProjectConfigurator::configureProject($this->newProject, new File($this->phingFile));

            if (null === $this->newTarget) {
                $this->newTarget = $this->newProject->getDefaultTarget();
            }

            // Are we trying to call the target in which we are defined?
            if (
                $this->newProject->getBasedir()->equals($this->project->getBasedir())
                && $this->newProject->getProperty('phing.file') === $this->project->getProperty('phing.file')
                && null !== $this->getOwningTarget()
            ) {
                $owningTargetName = $this->getOwningTarget()->getName();
                if ($this->newTarget === $owningTargetName) {
                    throw new BuildException(
                        sprintf(
                            '%s task calling its own parent target',
                            $this->getTaskName()
                        )
                    );
                }

                $targets = $this->getProject()->getTargets();
                $taskName = $this->getTaskName();

                foreach ($this->locals as $local) {
                    if (isset($targets[$local])) {
                        if ($targets[$local]->dependsOn($owningTargetName)) {
                            throw new BuildException(
                                sprintf(
                                    "%s task calling a target that depends on its parent target '%s'.",
                                    $taskName,
                                    $owningTargetName
                                )
                            );
                        }
                    }
                }
            }

            $this->addReferences();
            $this->newProject->executeTarget($this->newTarget);
        } catch (Exception $e) {
            $buildFailed = true;
            $buildFailedCause = $e;
            $this->log('[' . get_class($e) . '] ' . $e->getMessage(), Project::MSG_ERR);
            if (Phing::getMsgOutputLevel() <= Project::MSG_DEBUG) {
                $lines = explode("\n", $e->getTraceAsString());
                foreach ($lines as $line) {
                    $this->log($line, Project::MSG_DEBUG);
                }
            }
        } finally {
            // reset environment values to prevent side-effects.

            $this->newProject = null;
            $pkeys = array_keys($this->properties);
            foreach ($pkeys as $k) {
                $this->properties[$k]->setProject(null);
            }

            if (null !== $this->output && null !== $this->out) {
                $this->out->close();
            }

            $this->dir = $savedDir;
            $this->phingFile = $savedPhingFile;
            $this->newTarget = $savedTarget;

            // If the basedir for any project was changed, we need to set that back here.
            if (null !== $savedBasedirAbsPath) {
                chdir($savedBasedirAbsPath);
            }

            if ($this->haltOnFailure && $buildFailed) {
                throw new BuildException('Execution of the target buildfile failed. Aborting.', $buildFailedCause);
            }
        }
    }

    /**
     * Configure the Project, i.e. make intance, attach build listeners
     * (copy from father project), add Task and Datatype definitions,
     * copy properties and references from old project if these options
     * are set via the attributes of the XML tag.
     *
     * Developer note:
     * This function replaces the old methods "init", "_reinit" and
     * "_initializeProject".
     */
    private function initializeProject()
    {
        $this->newProject->setInputHandler($this->project->getInputHandler());

        foreach ($this->project->getBuildListeners() as $listener) {
            $this->newProject->addBuildListener($listener);
        }

        /* Copy things from old project. Datatypes and Tasks are always
         * copied, properties and references only if specified so/not
         * specified otherwise in the XML definition.
         */
        // Add Datatype definitions
        foreach ($this->project->getDataTypeDefinitions() as $typeName => $typeClass) {
            $this->newProject->addDataTypeDefinition($typeName, $typeClass);
        }

        // Add Task definitions
        foreach ($this->project->getTaskDefinitions() as $taskName => $taskClass) {
            if ('Phing\Task\System\PropertyTask' === $taskClass) {
                // we have already added this taskdef in init()
                continue;
            }
            $this->newProject->addTaskDefinition($taskName, $taskClass);
        }

        if (null !== $this->output) {
            try {
                if (null !== $this->dir) {
                    $outfile = (new FileUtils())->resolveFile($this->dir, $this->output);
                } else {
                    $outfile = $this->getProject()->resolveFile($this->output);
                }
                $this->out = new FileOutputStream($outfile);
                $logger = new DefaultLogger();
                $logger->setMessageOutputLevel(Project::MSG_INFO);
                $logger->setOutputStream($this->out);
                $logger->setErrorStream($this->out);
                $this->newProject->addBuildListener($logger);
            } catch (Exception $ex) {
                $this->log("Phing: Can't set output to " . $this->output);
            }
        }

        if ($this->useNativeBasedir) {
            $this->addAlmostAll($this->getProject()->getUserProperties(), 'user');
        } else {
            $this->project->copyUserProperties($this->newProject);
        }

        if (!$this->inheritAll) {
            // set System built-in properties separately,
            // b/c we won't inherit them.
            $this->newProject->setSystemProperties();
        } else {
            $this->addAlmostAll($this->getProject()->getProperties(), 'plain');
        }
    }

    /**
     * Copies all properties from the given table to the new project -
     * omitting those that have already been set in the new project as
     * well as properties named basedir or phing.file.
     *
     * @param array  $props properties <code>Hashtable</code> to copy to the
     *                      new project
     * @param string $type  the type of property to set (a plain Phing property, a
     *                      user property or an inherited property)
     */
    private function addAlmostAll(array $props, string $type): void
    {
        foreach ($props as $name => $value) {
            if ('basedir' === $name || 'phing.file' === $name || 'phing.version' === $name) {
                // basedir and phing.file get special treatment in main()
                continue;
            }
            if ('plain' === $type) {
                // don't re-set user properties, avoid the warning message
                if (null === $this->newProject->getProperty($name)) {
                    // no user property
                    $this->newProject->setNewProperty($name, $value);
                }
            } elseif ('user' === $type) {
                $this->newProject->setUserProperty($name, $value);
            } elseif ('inherited' === $type) {
                $this->newProject->setInheritedProperty($name, $value);
            }
        }
    }

    /**
     * Override the properties in the new project with the one
     * explicitly defined as nested elements here.
     *
     * @throws BuildException
     */
    private function overrideProperties()
    {
        // remove duplicate properties - last property wins
        $properties = array_reverse($this->properties);
        $set = [];
        foreach ($properties as $i => $p) {
            if (null !== $p->getName() && '' !== $p->getName()) {
                if (in_array($p->getName(), $set)) {
                    unset($this->properties[$i]);
                } else {
                    $set[] = $p->getName();
                }
            }
            $p->setProject($this->newProject);
            $p->main();
        }
        if ($this->useNativeBasedir) {
            $this->addAlmostAll($this->getProject()->getInheritedProperties(), 'inherited');
        } else {
            $this->project->copyInheritedProperties($this->newProject);
        }
    }

    /**
     * Add the references explicitly defined as nested elements to the
     * new project.  Also copy over all references that don't override
     * existing references in the new project if inheritrefs has been
     * requested.
     *
     * @throws BuildException
     */
    private function addReferences()
    {
        // parent project references
        $projReferences = $this->project->getReferences();

        $newReferences = $this->newProject->getReferences();

        $subprojRefKeys = [];

        if (count($this->references) > 0) {
            for ($i = 0, $count = count($this->references); $i < $count; ++$i) {
                $ref = $this->references[$i];
                $refid = $ref->getRefId();

                if (null === $refid) {
                    throw new BuildException('the refid attribute is required for reference elements');
                }
                if (!isset($projReferences[$refid])) {
                    $this->log("Parent project doesn't contain any reference '" . $refid . "'", Project::MSG_WARN);

                    continue;
                }

                $subprojRefKeys[] = $refid;
                unset($this->references[$i]); //thisReferences.remove(refid);
                $toRefid = $ref->getToRefid();
                if (null === $toRefid) {
                    $toRefid = $refid;
                }
                $this->copyReference($refid, $toRefid);
            }
        }

        // Now add all references that are not defined in the
        // subproject, if inheritRefs is true
        if ($this->inheritRefs) {
            // get the keys that are were not used by the subproject
            $unusedRefKeys = array_diff(array_keys($projReferences), $subprojRefKeys);

            foreach ($unusedRefKeys as $key) {
                if (isset($newReferences[$key])) {
                    continue;
                }
                $this->copyReference($key, $key);
            }
        }
    }

    /**
     * Try to clone and reconfigure the object referenced by oldkey in
     * the parent project and add it to the new project with the key
     * newkey.
     *
     * <p>If we cannot clone it, copy the referenced object itself and
     * keep our fingers crossed.</p>
     *
     * @param string $oldKey
     * @param string $newKey
     *
     * @throws BuildException
     */
    private function copyReference($oldKey, $newKey)
    {
        $orig = $this->project->getReference($oldKey);
        if (null === $orig) {
            $this->log(
                'No object referenced by ' . $oldKey . ". Can't copy to "
                . $newKey,
                Project::MSG_WARN
            );

            return;
        }

        $copy = clone $orig;

        if ($copy instanceof ProjectComponent) {
            $copy->setProject($this->newProject);
        } elseif (in_array('setProject', get_class_methods(get_class($copy)))) {
            $copy->setProject($this->newProject);
        } elseif (!($copy instanceof Project)) {
            // don't copy the old "Project" itself
            $msg = 'Error setting new project instance for '
                . 'reference with id ' . $oldKey;

            throw new BuildException($msg);
        }

        $this->newProject->addReference($newKey, $copy);
    }
}
