// resources/js/types/index.ts

export interface Transaction {
    id: number;
    user_id: number;
    description: string;
    amount: number;
    type: 'income' | 'expense';
    category: string;
    created_at: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    created_at?: string;
    updated_at?: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}