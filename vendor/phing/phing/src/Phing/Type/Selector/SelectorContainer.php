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

namespace Phing\Type\Selector;

use Phing\Project;

/**
 * This is the interface for selectors that can contain other selectors.
 *
 * @author <a href="mailto:bruce@callenish.com">Bruce Atherton</a>
 */
interface SelectorContainer
{
    /**
     * Indicates whether there are any selectors here.
     *
     * @return bool whether any selectors are in this container
     */
    public function hasSelectors();

    /**
     * Gives the count of the number of selectors in this container.
     *
     * @return int the number of selectors in this container
     */
    public function count();

    /**
     * Returns a *copy* of the set of selectors as an array.
     *
     * @return BaseSelectorContainer[] an array of selectors in this container
     */
    public function getSelectors(Project $p);

    /**
     * Returns an array for accessing the set of selectors.
     *
     * @return BaseSelectorContainer[] an enumerator that goes through each of the selectors
     */
    public function selectorElements();

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector $selector the new selector to add
     */
    public function appendSelector(FileSelector $selector);

    // Methods below all add specific selectors

    /**
     * add a "Select" selector entry on the selector list.
     */
    public function addSelector(SelectSelector $selector);

    /**
     * add an "And" selector entry on the selector list.
     */
    public function addAnd(AndSelector $selector);

    /**
     * add an "Or" selector entry on the selector list.
     */
    public function addOr(OrSelector $selector);

    /**
     * add a "Not" selector entry on the selector list.
     */
    public function addNot(NotSelector $selector);

    /**
     * add a "None" selector entry on the selector list.
     */
    public function addNone(NoneSelector $selector);

    /**
     * add a majority selector entry on the selector list.
     */
    public function addMajority(MajoritySelector $selector);

    /**
     * add a selector date entry on the selector list.
     */
    public function addDate(DateSelector $selector);

    /**
     * add a selector size entry on the selector list.
     */
    public function addSize(SizeSelector $selector);

    /**
     * add a selector filename entry on the selector list.
     */
    public function addFilename(FilenameSelector $selector);

    /**
     * add an extended selector entry on the selector list.
     */
    public function addCustom(ExtendSelector $selector);

    /**
     * add a contains selector entry on the selector list.
     */
    public function addContains(ContainsSelector $selector);

    /**
     * add a contains selector entry on the selector list.
     */
    public function addContainsRegexp(ContainsRegexpSelector $selector);

    /**
     * add a present selector entry on the selector list.
     */
    public function addPresent(PresentSelector $selector);

    /**
     * add a depth selector entry on the selector list.
     */
    public function addDepth(DepthSelector $selector);

    /**
     * add a depends selector entry on the selector list.
     */
    public function addDepend(DependSelector $selector);

    /**
     * add a different selector entry on the selector list.
     */
    public function addDifferent(DifferentSelector $selector);

    /**
     * add a modified selector entry on the selector list.
     */
    public function addModified(ModifiedSelector $selector);

    /**
     * add a type selector entry on the selector list.
     */
    public function addType(TypeSelector $selector);

    /**
     * add a executable selector entry on the selector list.
     */
    public function addExecutable(ExecutableSelector $selector);

    /**
     * add a readable selector entry on the selector list.
     */
    public function addReadable(ReadableSelector $selector);

    /**
     * add a writable selector entry on the selector list.
     */
    public function addWritable(WritableSelector $selector);

    /**
     * add a symlink selector entry on the selector list.
     */
    public function addSymlink(SymlinkSelector $selector);
}
