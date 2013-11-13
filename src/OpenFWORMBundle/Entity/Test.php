<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/13/13
 * @time 10:19 AM
 */

namespace OpenFWORMBundle\Entity;

/**
 * @Entity
 * @HasLifecycleCallbacks
 */
class Test
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    protected $id = null;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $email;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $username;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @PostPersist
     */
    public function sendOptinMail() {   }
} 