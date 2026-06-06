const FLAGCDN_WIDTHS = [20, 40, 80, 160, 320, 640, 1280, 2560] as const;

const TEAM_FLAG_CODES: Record<string, string> = {
    Algeria: 'dz',
    Argentina: 'ar',
    Australia: 'au',
    Austria: 'at',
    Belgium: 'be',
    'Bosnia & Herzegovina': 'ba',
    Brazil: 'br',
    Canada: 'ca',
    'Cape Verde': 'cv',
    Colombia: 'co',
    Croatia: 'hr',
    Curaçao: 'cw',
    'Czech Republic': 'cz',
    'DR Congo': 'cd',
    Ecuador: 'ec',
    Egypt: 'eg',
    England: 'gb-eng',
    France: 'fr',
    Germany: 'de',
    Ghana: 'gh',
    Haiti: 'ht',
    Iran: 'ir',
    Iraq: 'iq',
    'Ivory Coast': 'ci',
    Japan: 'jp',
    Jordan: 'jo',
    Mexico: 'mx',
    Morocco: 'ma',
    Netherlands: 'nl',
    'New Zealand': 'nz',
    Norway: 'no',
    Panama: 'pa',
    Paraguay: 'py',
    Portugal: 'pt',
    Qatar: 'qa',
    'Saudi Arabia': 'sa',
    Scotland: 'gb-sct',
    Senegal: 'sn',
    'South Africa': 'za',
    'South Korea': 'kr',
    Spain: 'es',
    Sweden: 'se',
    Switzerland: 'ch',
    Tunisia: 'tn',
    Turkey: 'tr',
    USA: 'us',
    Uruguay: 'uy',
    Uzbekistan: 'uz',
};

function flagcdnWidth(requested: number): number {
    return (
        FLAGCDN_WIDTHS.find((width) => width >= requested) ??
        FLAGCDN_WIDTHS[FLAGCDN_WIDTHS.length - 1]
    );
}

export function teamFlagUrl(teamName: string, width = 80): string | null {
    const code = TEAM_FLAG_CODES[teamName];

    if (!code) {
        return null;
    }

    return `https://flagcdn.com/w${flagcdnWidth(width)}/${code}.png`;
}

export function teamInitials(teamName: string): string {
    if (/^[WL]\d/.test(teamName) || /^\d/.test(teamName)) {
        return '?';
    }

    return teamName
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
}

export function isPlaceholderTeam(teamName: string): boolean {
    return teamFlagUrl(teamName) === null;
}
