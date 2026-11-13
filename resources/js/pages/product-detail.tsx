import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import CatalogueLayout from '@/layouts/CatalogueLayout';
import EnquiryForm from '@/components/EnquiryForm';
import { Product } from '@/types/catalogue';

function formatUsd(value: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
}

export default function ProductDetail({ product }: { product: Product }) {
    const [enquiring, setEnquiring] = useState(false);

    return (
        <CatalogueLayout>
            <Head title={product.title} />

            <nav
                aria-label="Breadcrumb"
                className="mb-6 text-sm text-slate-500"
            >
                <Link href="/" className="hover:text-blue-600">
                    Home
                </Link>
                {' / '}
                <Link
                    href={`/categories/${encodeURIComponent(product.category)}`}
                    className="capitalize hover:text-blue-600"
                >
                    {product.category}
                </Link>
                {' / '}
                <span className="text-slate-700">{product.title}</span>
            </nav>

            <div className="grid grid-cols-1 gap-10 md:grid-cols-2">
                <div className="flex items-center justify-center rounded-lg border border-slate-200 bg-white p-8">
                    <img
                        src={product.image}
                        alt={product.title}
                        className="max-h-96 object-contain"
                    />
                </div>

                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">
                        {product.title}
                    </h1>
                    <p className="mt-1 text-sm text-slate-500 capitalize">
                        {product.category}
                    </p>
                    <p className="mt-4 text-xl font-semibold text-slate-900">
                        {formatUsd(product.price)}
                    </p>
                    <p className="mt-1 text-sm text-slate-500">
                        ★ {product.rating.rate.toFixed(1)} (
                        {product.rating.count} reviews)
                    </p>
                    <p className="mt-4 text-slate-700">{product.description}</p>

                    {!enquiring ? (
                        <button
                            type="button"
                            onClick={() => setEnquiring(true)}
                            className="mt-6 rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Enquire
                        </button>
                    ) : (
                        <div className="mt-6 border-t border-slate-200 pt-6">
                            <h2 className="mb-4 text-lg font-medium text-slate-800">
                                Enquire about this product
                            </h2>
                            <EnquiryForm productId={product.id} />
                        </div>
                    )}
                </div>
            </div>
        </CatalogueLayout>
    );
}
