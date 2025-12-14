<?php

namespace Sholokhov\FrontBoot\Generator\Extension\Strategy\ViteVue;

enum Feature: string
{
    case Default = 'default';
    case TypeScript = 'ts';
    case Router = 'router';
    case Jsx = 'jsx';
    case Pinia = 'pinia';
    case Vitest = 'vitest';
    case Cypress = 'cypress';
    case Playwright = 'playwright';
    case Eslint = 'eslint';
    case Prettier = 'prettier';
    case Oxlint = 'oxlint';
    case RolldownVite = 'rolldown-vite';

    public function getDescription(): string
    {
        return match($this) {
            $this::Default => 'Create a project with the default configuration without any additional features',
            $this::TypeScript => 'Add TypeScript support',
            $this::Jsx => 'Add JSX support',
            $this::Router => 'Add Vue Router for SPA development',
            $this::Pinia => 'Add Pinia for state management',
            $this::Vitest => 'Add Vitest for unit testing',
            $this::Cypress => 'Add Cypress for end-to-end testing. If used without --vitest, it will also add Cypress Component Testing',
            $this::Playwright => 'Add Playwright for end-to-end testing',
            $this::Eslint => 'Add ESLint for code quality',
            $this::Prettier => 'Add Prettier for code formatting',
            $this::Oxlint => 'Add Oxlint for code quality and formatting',
            $this::RolldownVite => 'Use Rolldown Vite instead of Vite for building the project',
        };
    }
}