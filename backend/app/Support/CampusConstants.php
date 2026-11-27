<?php

namespace App\Support;

/**
 * Shared campus-wide constants used across the admin portal.
 */
final class CampusConstants
{
    /** University email domain appended to student email usernames. */
    public const UNIVERSITY_EMAIL_DOMAIN = '@gmail.com';

    /** Prefix automatically prepended to every student roll number. */
    public const ROLL_NUMBER_PREFIX = 'TEST';

    /** Default password assigned to newly created students. */
    public const DEFAULT_STUDENT_PASSWORD = 'TEST@2026';
}
