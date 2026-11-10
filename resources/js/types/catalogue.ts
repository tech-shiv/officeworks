export interface Product {
    id: number;
    title: string;
    price: number;
    description: string;
    category: string;
    image: string;
    rating: {
        rate: number;
        count: number;
    };
}

export interface EnquiryFormData {
    product_id: number;
    name: string;
    email: string;
    phone: string;
    message: string;
    [key: string]: string | number;
}
