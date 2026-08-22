<?php

namespace App\Enums;

/**
 * Summary of NotificationCode
 */
enum NotificationCode: string
{
    case MESSAGE_RECEIVED = 'message_received';

    case ANNOUNCEMENT_PUBLISHED = 'announcement_published';

    case CALENDAR_EVENT_CREATED = 'calendar_event_created';
    case CALENDAR_EVENT_UPDATED = 'calendar_event_updated';
    case CALENDAR_EVENT_REMINDER = 'calendar_event_reminder';

    case PATIENT_CREATED = 'patient_created';
    case DENTAL_EXAMINATION_CREATED = 'dental_examination_created';
}
