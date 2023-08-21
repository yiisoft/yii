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

use Phing\Exception\BuildException;
use Phing\Filter\BaseFilterReader;
use Phing\Filter\ClassConstants;
use Phing\Filter\ConcatFilter;
use Phing\Filter\EscapeUnicode;
use Phing\Filter\ExpandProperties;
use Phing\Filter\HeadFilter;
use Phing\Filter\IconvFilter;
use Phing\Filter\LineContains;
use Phing\Filter\LineContainsRegexp;
use Phing\Filter\PhpArrayMapLines;
use Phing\Filter\PrefixLines;
use Phing\Filter\ReplaceRegexp;
use Phing\Filter\ReplaceTokens;
use Phing\Filter\ReplaceTokensWithFile;
use Phing\Filter\SortFilter;
use Phing\Filter\StripLineBreaks;
use Phing\Filter\StripLineComments;
use Phing\Filter\StripPhpComments;
use Phing\Filter\StripWhitespace;
use Phing\Filter\SuffixLines;
use Phing\Filter\TabToSpaces;
use Phing\Filter\TailFilter;
use Phing\Filter\TidyFilter;
use Phing\Filter\TranslateGettext;
use Phing\Filter\XincludeFilter;
use Phing\Filter\XsltFilter;

/**
 * FilterChain may contain a chained set of filter readers.
 *
 * @author  Yannick Lecaillez <yl@seasonfive.com>
 */
class FilterChain extends DataType
{
    private $filterReaders = [];

    /**
     * @param null $project
     */
    public function __construct($project = null)
    {
        parent::__construct();

        if ($project) {
            $this->project = $project;
        }
    }

    /**
     * @return array
     */
    public function getFilterReaders()
    {
        return $this->filterReaders;
    }

    public function addConcatFilter(ConcatFilter $o)
    {
        $this->add($o);
    }

    public function addExpandProperties(ExpandProperties $o)
    {
        $this->add($o);
    }

    public function addGettext(TranslateGettext $o)
    {
        $this->add($o);
    }

    public function addHeadFilter(HeadFilter $o)
    {
        $this->add($o);
    }

    public function addIconvFilter(IconvFilter $o)
    {
        $this->add($o);
    }

    public function addTailFilter(TailFilter $o)
    {
        $this->add($o);
    }

    public function addLineContains(LineContains $o)
    {
        $this->add($o);
    }

    public function addLineContainsRegExp(LineContainsRegexp $o)
    {
        $this->add($o);
    }

    public function addPrefixLines(PrefixLines $o)
    {
        $this->add($o);
    }

    public function addSuffixLines(SuffixLines $o)
    {
        $this->add($o);
    }

    public function addEscapeUnicode(EscapeUnicode $o)
    {
        $this->add($o);
    }

    public function addPhpArrayMapLines(PhpArrayMapLines $o)
    {
        $this->add($o);
    }

    public function addReplaceTokens(ReplaceTokens $o)
    {
        $this->add($o);
    }

    public function addReplaceTokensWithFile(ReplaceTokensWithFile $o)
    {
        $this->add($o);
    }

    public function addReplaceRegexp(ReplaceRegexp $o)
    {
        $this->add($o);
    }

    public function addStripPhpComments(StripPhpComments $o)
    {
        $this->add($o);
    }

    public function addStripLineBreaks(StripLineBreaks $o)
    {
        $this->add($o);
    }

    public function addStripLineComments(StripLineComments $o)
    {
        $this->add($o);
    }

    public function addStripWhitespace(StripWhitespace $o)
    {
        $this->add($o);
    }

    public function addTidyFilter(TidyFilter $o)
    {
        $this->add($o);
    }

    public function addTabToSpaces(TabToSpaces $o)
    {
        $this->add($o);
    }

    public function addXincludeFilter(XincludeFilter $o)
    {
        $this->add($o);
    }

    public function addXsltFilter(XsltFilter $o)
    {
        $this->add($o);
    }

    public function addFilterReader(FilterReader $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    public function addSortFilter(SortFilter $o)
    {
        $this->add($o);
    }

    public function addClassConstants(ClassConstants $o)
    {
        $this->add($o);
    }

    /*
     * Makes this instance in effect a reference to another FilterChain
     * instance.
     *
     * <p>You must not set another attribute or nest elements inside
     * this element if you make it a reference.</p>
     *
     * @param  $r the reference to which this instance is associated
     * @throws BuildException if this instance already has been configured.
    */

    /**
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {
        if (0 !== count($this->filterReaders)) {
            throw $this->tooManyAttributes();
        }

        // change this to get the objects from the other reference
        $o = $r->getReferencedObject($this->getProject());
        if ($o instanceof FilterChain) {
            $this->filterReaders = $o->getFilterReaders();
        } else {
            throw new BuildException($r->getRefId() . " doesn't refer to a FilterChain");
        }
        parent::setRefid($r);
    }

    private function add(BaseFilterReader $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }
}
