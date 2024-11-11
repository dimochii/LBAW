<?php

namespace App\Enums;

enum ReportType: string
{
    case UserReport = 'user_report';
    case CommentReport = 'comment_report';
    case ItemReport = 'item_report';
    case TopicReport = 'topic_report';
}
