import {BXInterface} from "./interfaces/bx-interface";

declare global {
    interface Window {
        BX: BXInterface,
        FrontBoot: FrontBootApi,
    }
}

export interface FrontBootApi {
    get extensions(): Map<string, any>;
    getMessage(name: string): string;
}