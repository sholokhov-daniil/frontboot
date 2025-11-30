import type { BX } from "../globals";

export interface LanguageInterface {
    get(name: string): string;
    has(name: string): boolean;
}

export class Language implements LanguageInterface {
    /**
     * Отдает текст языкового ключа
     *
     * @param {string} name
     * @returns {string}
     */
    get(name: string): string {
        let message = '';

        try {
            message = this.has(name) ? BX.Loc.getMessage(name) : ''
        } catch {
        }

        return message;
    }

    /**
     * Проверка наличия языкового ключа
     *
     * @param {string} name
     * @returns {boolean}
     */
    has(name: string): boolean {
        return BX.Loc.hasMessage(name);
    }
}