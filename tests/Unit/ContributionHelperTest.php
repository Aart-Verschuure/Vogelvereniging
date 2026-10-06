<?php

test('lid per de 1e van de maand na aanmelding + 3 weken', function () {
    // 5 maart + 3 weken = 26 maart => lid per 1 april
    expect(membership_start_date('2026-03-05')->toDateString())->toBe('2026-04-01');

    // 21 maart + 3 weken = 11 april => lid per 1 mei
    expect(membership_start_date('2026-03-21')->toDateString())->toBe('2026-05-01');
});

test('aantal contributiemaanden', function () {
    expect(contribution_months('2026-03-05'))->toBe(9);
    expect(contribution_months('2026-03-21'))->toBe(8);
});

test('valt aanmelding + 3 weken op de 1e, dan is iemand per die dag lid', function () {
    // 11 maart + 3 weken = 1 april
    expect(membership_start_date('2026-03-11')->toDateString())->toBe('2026-04-01');
});

test('aanmelding eind december wordt lid in het nieuwe jaar', function () {
    // 20 december + 3 weken = 10 januari => lid per 1 februari => 11 maanden
    expect(membership_start_date('2026-12-20')->toDateString())->toBe('2027-02-01');
    expect(contribution_months('2026-12-20'))->toBe(11);
});

test('contributie is de jaarprijs naar rato van het aantal maanden', function () {
    expect(calculate_contribution(36, '2026-03-05'))->toBe(27.0);
    expect(calculate_contribution(18, '2026-03-21'))->toBe(12.0);
    expect(calculate_contribution(18, '2026-03-05'))->toBe(13.5);
});

test('in het jaar dat je 18 wordt ben je nog jeugdlid', function () {
    // Geboren 1 januari 2008: wordt 18 op 1 januari 2026, betaalt in 2026 nog jeugdlid (een jaar voordeel)
    expect(is_youth_in_year('2008-01-01', 2026))->toBeTrue();
    expect(is_youth_in_year('2008-12-31', 2026))->toBeTrue();
    expect(is_youth_in_year('2008-01-01', 2027))->toBeFalse();
    expect(is_youth_in_year('2015-06-15', 2026))->toBeTrue();
    expect(is_youth_in_year('1970-01-01', 2026))->toBeFalse();
});
