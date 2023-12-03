<?php

declare(strict_types=1);

namespace Revolution\Nostr;

enum Kind: int
{
    case Metadata = 0;
    case Text = 1;
    case RecommendRelay = 2;
    case Contacts = 3;
    case EncryptedDirectMessage = 4;
    case EventDeletion = 5;
    case Reaction = 7;
    case BadgeAward = 8;
    case ChannelCreation = 40;
    case ChannelMetadata = 41;
    case ChannelMessage = 42;
    case ChannelHideMessage = 43;
    case ChannelMuteUser = 44;
    case Blank = 255;
    case FileMetadata = 1063;
    case Report = 1984;
    case ZapRequest = 9734;
    case Zap = 9735;
    case RelayList = 10002;
    case ClientAuth = 22242;
    case NwcRequest = 23194;
    case HttpAuth = 27235;
    case ProfileBadge = 30008;
    case BadgeDefinition = 30009;
    case Article = 30023;
}
