<?php

declare(strict_types=1);

namespace App\Enums;

enum ClubServicesType: string
{
    case TennisCourt = 'tennis_court';
    case Wifi = 'wifi';
    case Parking = 'parking';
    case Restaurant = 'restaurant';
    case Shower = 'shower';
    case BarbecueArea = 'barbecue_area';
    case Terrace = 'terrace';
    case EventRoom = 'event_room';
    case Tournaments = 'tournaments';
    case FirstAid = 'first_aid';
    case GroupClasses = 'group_classes';

    public function label(): string
    {
        return __('club-services.'.$this->value);
    }

    /**
     * Get the icon URL for the club service.
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::TennisCourt => asset('icons/baseline-sports-tennis.svg'),
            self::Wifi => asset('icons/baseline-wifi.svg'),
            self::Parking => asset('icons/outline-local-parking.svg'),
            self::Restaurant => asset('icons/outline-restaurant.svg'),
            self::Shower => asset('icons/baseline-shower.svg'),
            self::BarbecueArea => asset('icons/baseline-outdoor-grill.svg'),
            self::Terrace => asset('icons/baseline-deck.svg'),
            self::EventRoom => asset('icons/outline-cake.svg'),
            self::Tournaments => asset('icons/baseline-emoji-events.svg'),
            self::FirstAid => asset('icons/outline-health-and-safety.svg'),
            self::GroupClasses => asset('icons/round-groups.svg'),
        };
    }
}
