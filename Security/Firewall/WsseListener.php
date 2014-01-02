<?php

namespace Devster\WSSEBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Devster\WSSEBundle\Security\Authentication\Token\WsseUserToken;

class WsseListener implements ListenerInterface
{
    protected $isChained;

    protected $securityContext;

    protected $authenticationManager;

    public function __construct($isChained, SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
    {
        $this->isChained = $isChained;
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        try {
            $token = $this->defineToken($request);
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);

            return;
        } catch (AuthenticationException $failed) {
            $this->deny($failed);
            return;
        }
    }

    protected function defineToken(Request $request)
    {
        if (! $header = $request->headers->get('x-wsse')) {
            throw new BadCredentialsException('Unable to find the X-WSSE header');
        }

        $token = new WsseUserToken();

        // Match all we need to authenticate
        // We do it in several regex to be more flexible
        if (1 !== preg_match('/Username="([^"]+)"/', $header, $matches)) {
            throw new BadCredentialsException('Unable to find Username in X-WSSE header');
        }
        $token->setUser($matches[1]);

        if (1 !== preg_match('/PasswordDigest="([^"]+)"/', $header, $matches)) {
            throw new BadCredentialsException('Unable to find PasswordDigest in X-WSSE header');
        }
        $token->digest = $matches[1];

        if (1 !== preg_match('/Nonce="([^"]+)"/', $header, $matches)) {
            throw new BadCredentialsException('Unable to find Nonce in X-WSSE header');
        }
        $token->nonce = $matches[1];

        if (1 !== preg_match('/Created="([^"]+)"/', $header, $matches)) {
            throw new BadCredentialsException('Unable to find Created in X-WSSE header');
        }
        $token->created = $matches[1];

        return $token;
    }

    protected function deny(\Exception $previous)
    {
        if (! $this->isChained) {
            throw new AccessDeniedHttpException(
                sprintf('Access denied: %s', $previous->getMessage())
            );
        }
    }
}
