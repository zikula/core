<?php
/*
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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Event;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\EntityManager;

/**
 * Class that holds event arguments for a loadMetadata event.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @since  2.0
 */
class LoadClassMetadataEventArgs extends EventArgs
{
    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $classMetadata;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $classMetadata
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(ClassMetadataInfo $classMetadata, EntityManager $em)
    {
        $this->classMetadata = $classMetadata;
        $this->em            = $em;
    }

    /**
     * Retrieve associated ClassMetadata.
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadataInfo
     */
    public function getClassMetadata()
    {
        return $this->classMetadata;
    }

    /**
     * Retrieve associated EntityManager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }
}

