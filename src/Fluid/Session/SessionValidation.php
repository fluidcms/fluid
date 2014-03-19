<?php
namespace Fluid\Session;

class SessionValidation
{
    /**
     * @param SessionEntity $session
     * @param SessionCollection $sessionCollection
     * @return SessionEntity|bool
     */
    public static function validate(SessionEntity $session, SessionCollection $sessionCollection)
    {
        if ($session->isExpired()) {
            $sessionCollection->delete($session);
            return false;
        }

        return $session;
    }
}