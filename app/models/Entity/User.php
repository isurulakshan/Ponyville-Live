<?php
namespace Entity;

use \Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * @Table(name="users")
 * @Entity
 */
class User extends \DF\Doctrine\Entity
{
    public function __construct()
    {
        $this->roles = new ArrayCollection;
        $this->external_accounts = new ArrayCollection;

        $this->stations = new ArrayCollection;
        $this->podcasts = new ArrayCollection;

        $this->time_created = time();
        $this->time_updated = time();
    }

    /**
     * @Column(name="uid", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @Column(name="email", type="string", length=100, nullable=true) */
    protected $email;

    public function getAvatar($size = 50)
    {
        return \DF\Service\Gravatar::get($this->email, $size, 'identicon');
    }

    /** @Column(name="auth_password", type="string", length=255, nullable=true) */
    protected $auth_password;

    /** @Column(name="auth_password_salt", type="string", length=255, nullable=true) */
    protected $auth_password_salt;

    /** @Column(name="auth_external_provider", type="string", length=255, nullable=true) */
    protected $auth_external_provider;

    /** @Column(name="auth_external_id", type="string", length=255, nullable=true) */
    protected $auth_external_id;

    public function getAuthPassword()
    {
        return '';
    }

    public function setAuthPassword($password)
    {
        if (trim($password))
        {
            $this->auth_password_salt = 'PHP';
            $this->auth_password = password_hash($password, \PASSWORD_DEFAULT);
        }

        return $this;
    }

    public function generateRandomPassword()
    {
        $this->setAuthPassword(md5('PVL_EXTERNAL_'.mt_rand(0, 10000)));
    }

    /** @Column(name="auth_last_login_time", type="integer", nullable=true) */
    protected $auth_last_login_time;

    /** @Column(name="auth_recovery_code", type="string", length=50, nullable=true) */
    protected $auth_recovery_code;

    public function generateAuthRecoveryCode()
    {
        $this->auth_recovery_code = sha1(mt_rand());
        return $this->auth_recovery_code;
    }

    /** @Column(name="name", type="string", length=100, nullable=true) */
    protected $name;

    /** @Column(name="legal_name", type="string", length=100, nullable=true) */
    protected $legal_name;

    /** @Column(name="pony_name", type="string", length=100, nullable=true) */
    protected $pony_name;

    /** @Column(name="phone", type="string", length=50, nullable=true) */
    protected $phone;

    /** @Column(name="pvl_affiliation", type="string", length=50, nullable=true) */
    protected $pvl_affiliation;

    /** @Column(name="title", type="string", length=100, nullable=true) */
    protected $title;

    /** @Column(name="gender", type="string", length=1, nullable=true) */
    protected $gender;

    /** @Column(name="customization", type="json", nullable=true) */
    protected $customization;

    /**
     * @ManyToMany(targetEntity="Role", inversedBy="users")
     * @JoinTable(name="user_has_role",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="uid", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $roles;

    /**
     * @ManyToMany(targetEntity="Station", inversedBy="users")
     * @JoinTable(name="user_manages_station",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="uid", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn(name="station_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $stations;

    /**
     * @ManyToMany(targetEntity="Podcast", inversedBy="managers")
     * @JoinTable(name="user_manages_podcast",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="uid", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn(name="podcast_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $podcasts;

    /**
     * @OneToMany(targetEntity="UserExternal", mappedBy="user")
     */
    protected $external_accounts;

    /**
     * Static Functions
     */
    
    public static function authenticate($username, $password)
    {
        $login_info = self::getRepository()->findOneBy(array('email' => $username));

        if (!($login_info instanceof self))
            return FALSE;

        // Check for newer password style.
        if ($login_info->auth_password_salt === 'PHP')
        {
            if (password_verify($password, $login_info->auth_password))
            {
                if (password_needs_rehash($login_info->auth_password, \PASSWORD_DEFAULT))
                    $login_info->setAuthPassword($password)->save();

                return $login_info;
            }
        }
        else {
            $hashed_password = sha1($password . $login_info->auth_password_salt);

            if (strcasecmp($hashed_password, $login_info->auth_password) == 0)
            {
                // Force reset of password into newer format.
                $login_info->setAuthPassword($password)->save();

                return $login_info;
            }
        }

        return FALSE;
    }

    /**
     * Creates or returns an existing user with the specified e-mail address.
     *
     * @param $email
     * @return User
     */
    public static function getOrCreate($email)
    {
        $user = User::getRepository()->findOneBy(array('email' => $email));

        if (!($user instanceof User))
        {
            $user = new User;
            $user->email = $email;
            $user->name = $email;
        }

        return $user;
    }
}
