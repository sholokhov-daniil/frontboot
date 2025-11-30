import { Language, LanguageInterface } from "./lang/language";
import type { FrontBootApi } from './globals';

window.FrontBoot  = new class Core implements FrontBootApi{
    #extensions: Map<string, any>;
    #lang: LanguageInterface;

    constructor() {
        this.#extensions = new Map;
        this.#lang = new Language;
    }

    getMessage(name: string): string {
        return this.lang.get(name);
    }

    get lang(): LanguageInterface {
        return this.#lang;
    }

    get extensions(): Map<string, object|Function> {
        return this.#extensions;
    }
}