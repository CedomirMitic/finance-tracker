// resources/js/types/index.ts

export interface PageProps {
    [key: string]: unknown;
    auth: {
        user: User;
    };
    flash: {
        success?: string | null;
        error?: string | null;
    }
}

export interface Transaction {
    id: number;
    user_id: number;
    description: string;
    amount: number;
    type: 'income' | 'expense';
    payment_type?: 'manual' | 'recurring';
    currency: string;
    billing_day?: number;
    category: string;
    created_at: string;
    original_amount?: number;
    original_currency?: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    preferred_currency: string;
    email_verified_at?: string;
    subscribed?:boolean;
    created_at?: string;
    updated_at?: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    links: { 
        url: string | null; 
        label: string; 
        active: boolean 
    }[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}