<?php

namespace Phing;

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
interface TypeAdapter
{
    /**
     * Sets the project.
     *
     * @param Project $p the project instance
     */
    public function setProject(Project $p);

    /**
     * Gets the project.
     *
     * @return Project the project instance
     */
    public function getProject();

    /**
     * Sets the proxy object, whose methods are going to be
     * invoked by ant.
     * A proxy object is normally the object defined by
     * a &lt;typedef/&gt; task that is adapted by the "adapter"
     * attribute.
     *
     * @param object $o The target object. Must not be <code>null</code>.
     */
    public function setProxy($o);

    /**
     * Returns the proxy object.
     *
     * @return mixed the target proxy object
     */
    public function getProxy();
}
