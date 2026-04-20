<?php

namespace App\Enums;

enum Module: string
{
    case ActivityTypes = 'activity_types';
    case Activities = 'activities';
    case CellGroups = 'cell_groups';
    case Classifications = 'classifications';
    case Ministries = 'ministries';
    case Departments = 'departments';
    case Pastors = 'pastors';
    case Participants = 'participants';
    case SiActivityCategories = 'si_activity_categories';
    case SiMembers = 'si_members';
    case SiActivities = 'si_activities';
    case Posts = 'posts';
}
