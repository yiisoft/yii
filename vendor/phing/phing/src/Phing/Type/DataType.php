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

namespace Phing\Type;

use Phing\ComponentHelper;
use Phing\Exception\BuildException;
use Phing\Project;
use Phing\ProjectComponent;

/**
 * Base class for those classes that can appear inside the build file
 * as stand alone data types.
 *
 * This class handles the common description attribute and provides
 * a default implementation for reference handling and checking for
 * circular references that is appropriate for types that can not be
 * nested inside elements of the same type (i.e. patternset but not path)
 *
 * {@inheritdoc}
 */
class DataType extends ProjectComponent
{
    /**
     * Are we sure we don't hold circular references?
     *
     * Subclasses are responsible for setting this value to false
     * if we'd need to investigate this condition (usually because a
     * child element has been added that is a subclass of DataType).
     *
     * @var bool
     */
    protected $checked = true;
    /**
     * Value to the refid attribute.
     *
     * @var Reference
     */
    private $ref;

    /**
     * Basic DataType toString().
     *
     * @return string this DataType formatted as a String
     */
    public function __toString()
    {
        $d = $this->getDescription();

        return null === $d ? $this->getDataTypeName() : $this->getDataTypeName() . ' ' . $d;
    }

    /**
     * Has the refid attribute of this element been set?
     *
     * @return bool
     */
    public function isReference()
    {
        return null !== $this->ref;
    }

    /**
     * @return null|string
     */
    public function getRefId()
    {
        return null !== $this->ref ? $this->ref->getRefId() : null;
    }

    /**
     * Set the value of the refid attribute.
     *
     * Subclasses may need to check whether any other attributes
     * have been set as well or child elements have been created and
     * thus override this method. if they do they must call parent::setRefid()
     */
    public function setRefid(Reference $r)
    {
        $this->ref = $r;
        $this->checked = false;
    }

    /**
     * @param bool $checked
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

    /**
     * Check to see whether any DataType we hold references to is
     * included in the Stack (which holds all DataType instances that
     * directly or indirectly reference this instance, including this
     * instance itself).
     *
     * If one is included, throw a BuildException created by circularReference
     *
     * This implementation is appropriate only for a DataType that
     * cannot hold other DataTypes as children.
     *
     * The general contract of this method is that it shouldn't do
     * anything if checked is true and set it to true on exit.
     *
     * @param array   $stk
     * @param Project $p
     *
     * @throws BuildException
     */
    public function dieOnCircularReference(&$stk, Project $p = null)
    {
        if ($this->checked || !$this->isReference()) {
            return;
        }

        $o = $this->ref->getReferencedObject($p);

        if ($o instanceof DataType) {
            // TESTME - make sure that in_array() works just as well here
            //
            // check if reference is in stack
            //$contains = false;
            //for ($i=0, $size=count($stk); $i < $size; $i++) {
            //    if ($stk[$i] === $o) {
            //        $contains = true;
            //        break;
            //    }
            //}

            if (in_array($o, $stk, true)) {
                // throw build exception
                throw $this->circularReference();
            }

            $stk[] = $o;
            $o->dieOnCircularReference($stk, $p);
            array_pop($stk);
        }
        $this->checked = true;
    }

    public static function pushAndInvokeCircularReferenceCheck(DataType $dt, &$stk, Project $p)
    {
        $stk[] = $dt;
        $dt->dieOnCircularReference($stk, $p);
        array_pop($stk);
    }

    /**
     * Performs the check for circular references and returns the referenced object.
     *
     * @param string $requiredClass
     * @param string $dataTypeName
     *
     * @throws BuildException
     */
    public function getCheckedRef($requiredClass, $dataTypeName)
    {
        if (!$this->checked) {
            // should be in stack
            $stk = [];
            $stk[] = $this;
            $this->dieOnCircularReference($stk, $this->getProject());
        }

        $o = $this->ref->getReferencedObject($this->getProject());
        if (!($o instanceof $requiredClass)) {
            throw new BuildException($this->ref->getRefId() . " doesn't denote a " . $dataTypeName);
        }

        return $o;
    }

    /**
     * Creates an exception that indicates that refid has to be the
     * only attribute if it is set.
     *
     * @return BuildException
     */
    public function tooManyAttributes()
    {
        return new BuildException('You must not specify more than one attribute when using refid');
    }

    /**
     * Creates an exception that indicates that this XML element must
     * not have child elements if the refid attribute is set.
     *
     * @return BuildException
     */
    public function noChildrenAllowed()
    {
        return new BuildException('You must not specify nested elements when using refid');
    }

    /**
     * Creates an exception that indicates the user has generated a
     * loop of data types referencing each other.
     *
     * @return BuildException
     */
    public function circularReference()
    {
        return new BuildException('This data type contains a circular reference.');
    }

    /**
     * Template method being called when the data type has been
     * parsed completely.
     *
     * {@inheritdoc}
     */
    public function parsingComplete()
    {
    }

    /**
     * Gets as descriptive as possible a name used for this datatype instance.
     *
     * @return string name
     */
    protected function getDataTypeName()
    {
        return ComponentHelper::getElementName($this->getProject(), $this, true);
    }
}
