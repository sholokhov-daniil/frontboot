export interface BXInterface {
    Loc(): BXLang;
}

export interface BXLang {
    getMessage(name: string): string;
}