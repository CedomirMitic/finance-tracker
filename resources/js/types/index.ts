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

// Ako imaš i User interfejs, i on mora imati export
export interface User {
    id: number;
    name: string;
    email: string;
}