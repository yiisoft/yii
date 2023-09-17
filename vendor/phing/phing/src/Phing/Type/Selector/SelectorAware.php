<?php

namespace Phing\Type\Selector;

use Phing\Project;

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
trait SelectorAware
{
    /**
     * @var BaseSelectorContainer[]
     */
    protected $selectorsList = [];

    /**
     * Indicates whether there are any selectors here.
     */
    public function hasSelectors()
    {
        return !empty($this->selectorsList);
    }

    /**
     * Gives the count of the number of selectors in this container.
     */
    public function count()
    {
        return count($this->selectorsList);
    }

    /**
     * Returns a copy of the selectors as an array.
     *
     * @return array
     */
    public function getSelectors(Project $p)
    {
        $result = [];
        for ($i = 0, $size = count($this->selectorsList); $i < $size; ++$i) {
            $result[] = clone $this->selectorsList[$i];
        }

        return $result;
    }

    /**
     * Returns an array for accessing the set of selectors (not a copy).
     */
    public function selectorElements()
    {
        return $this->selectorsList;
    }

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector $selector new selector to add
     */
    public function appendSelector(FileSelector $selector)
    {
        $this->selectorsList[] = $selector;
    }

    /**
     * add a "Select" selector entry on the selector list.
     */
    public function addSelector(SelectSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add an "And" selector entry on the selector list.
     */
    public function addAnd(AndSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add an "Or" selector entry on the selector list.
     */
    public function addOr(OrSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a "Not" selector entry on the selector list.
     */
    public function addNot(NotSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a "None" selector entry on the selector list.
     */
    public function addNone(NoneSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a majority selector entry on the selector list.
     */
    public function addMajority(MajoritySelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a selector date entry on the selector list.
     */
    public function addDate(DateSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a selector size entry on the selector list.
     */
    public function addSize(SizeSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a selector filename entry on the selector list.
     */
    public function addFilename(FilenameSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add an extended selector entry on the selector list.
     */
    public function addCustom(ExtendSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a contains selector entry on the selector list.
     */
    public function addContains(ContainsSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a contains selector entry on the selector list.
     */
    public function addContainsRegexp(ContainsRegexpSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a present selector entry on the selector list.
     */
    public function addPresent(PresentSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a depth selector entry on the selector list.
     */
    public function addDepth(DepthSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a depends selector entry on the selector list.
     */
    public function addDepend(DependSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a different selector entry on the selector list.
     */
    public function addDifferent(DifferentSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a modified selector entry on the selector list.
     */
    public function addModified(ModifiedSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a type selector entry on the selector list.
     */
    public function addType(TypeSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a executable selector entry on the selector list.
     */
    public function addExecutable(ExecutableSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a readable selector entry on the selector list.
     */
    public function addReadable(ReadableSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a writable selector entry on the selector list.
     */
    public function addWritable(WritableSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a symlink selector entry on the selector list.
     */
    public function addSymlink(SymlinkSelector $selector)
    {
        $this->appendSelector($selector);
    }

    /**
     * add a symlink selector entry on the selector list.
     */
    public function addPosixPermissions(PosixPermissionsSelector $selector)
    {
        $this->appendSelector($selector);
    }
}
