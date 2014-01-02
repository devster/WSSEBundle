<?php

namespace Devster\WSSEBundle\Security\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Devster\WSSEBundle\Security\Authentication\Token\WsseUserToken;
use Devster\WSSEBundle\Security\Authentication\Nonce\NonceRepositoryProvider;

class WsseProvider implements AuthenticationProviderInterface
{
    protected $userProvider;

    protected $config;

    protected $nonceRepository;

    public function __construct(UserProviderInterface $userProvider, $config, NonceRepositoryProvider $nonceProvider)
    {
        $this->userProvider = $userProvider;
        $this->config = $config;

        $nonceConfig = $config['nonce'];

        $this->nonceRepository = $nonceProvider->get(array_keys($nonceConfig)[0]);

        if (null !==  ($params = array_values($nonceConfig)[0])) {
            $this->nonceRepository->configure($params);
        }
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if ($user && $this->validateDigest($token->getUsername(), $token->digest, $token->nonce, $token->created, $user->getPassword())) {
            $authenticatedToken = new WsseUserToken($user->getRoles());
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The WSSE authentication failed.');
    }

    protected function validateDigest($username, $digest, $nonce, $created, $secret)
    {
        // Check created time is not in the future
        // var_dump($created, (new \DateTime())->format('Y-m-d H:i:s'));die;
        if (strtotime($created) > time()) {
            return false;
        }

        // Expire timestamp after the lifetime
        if (time() - strtotime($created) > $this->config['lifetime']) {
            return false;
        }

        // Validate that the nonce is *not* used
        // if it has, this could be a replay attack
        // We prefix the nonce with the username to avoid collisions between user nonces
        $_nonce = sprintf('%s_%s', $username, $nonce);
        if ($this->nonceRepository->exists($_nonce)) {
            throw new NonceExpiredException('Previously used nonce detected');
        }

        // else we save the nonce
        $this->nonceRepository->save($_nonce);

        // Validate Secret
        $expected = base64_encode(sha1($nonce.$created.$secret, true));

        return $digest === $expected;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof WsseUserToken;
    }
}
