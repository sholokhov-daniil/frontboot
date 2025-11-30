import { Language, LanguageInterface } from "./lang/language";
import type { FrontBootApi } from './globals';

class Core implements FrontBootApi{
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

const FrontBoot = new Core;

if (typeof window !== 'undefined') {
    (window as any).FrontBoot = FrontBoot;
}