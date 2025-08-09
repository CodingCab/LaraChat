# IMPORTANT - AGENTS.md 
Read AGENTS.md for more instructions

# CLAUDE.md
This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Essential Commands

### Development
- `npm run dev` - Start Vite development server
- `composer dev` - Run Laravel server, queue listener, logs, and Vite concurrently
- `composer dev:ssr` - Development mode with server-side rendering

### Build & Production
- `npm run build` - Production build
- `npm run build:ssr` - Server-side rendering build

### Code Quality
- `npm run lint` - Run ESLint with auto-fix
- `npm run format` - Format code with Prettier
- `npm run format:check` - Check formatting without changes
- `composer test` - Run PHPUnit tests

## Architecture Overview

This is a **Laravel 12 + Vue 3 + Inertia.js** application with server-side rendering support.

### Tech Stack
- **Backend**: Laravel 12 with Inertia.js
- **Frontend**: Vue 3 with TypeScript
- **UI**: Tailwind CSS 4 with shadcn/ui component patterns
- **State**: Inertia.js built-in state management
- **Database**: SQLite (default)

### Key Directories
- `resources/js/pages/` - Inertia page components
- `resources/js/layouts/` - Page layouts (AppLayout, AuthLayout)
- `resources/js/components/ui/` - Reusable UI components (shadcn/ui pattern)
- `resources/js/composables/` - Vue composition API utilities
- `app/Http/Controllers/` - Laravel controllers
- `routes/web.php` - Web routes (Inertia pages)
- `routes/api.php` - API routes

### Important Patterns

1. **Page Components**: Use `AppLayout` for authenticated pages with navigation:
```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Page Name', href: '/page-name' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Page content -->
    </AppLayout>
</template>
```

2. **TypeScript Path Alias**: `@/` maps to `resources/js/`

3. **Authentication**: Full auth system with registration, login, password reset, email verification

4. **UI Components**: Located in `resources/js/components/ui/`, following shadcn/ui patterns

## Security Warning


## Development Notes

- The app includes a Dashboard and Claude chat page
- Navigation is configured in `AppSidebar.vue` and `AppHeader.vue`
- Theme support (light/dark) is built-in with appearance management
- Prettier is configured with Tailwind CSS class sorting
- ESLint is configured for Vue 3 and TypeScript

## Important Reminders
- This is important: always read AGENTS.md for more instructions
