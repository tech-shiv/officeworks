import { useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { EnquiryFormData } from '@/types/catalogue';

export default function EnquiryForm({ productId }: { productId: number }) {
    const [submitted, setSubmitted] = useState(false);
    const { data, setData, post, processing, errors, reset } =
        useForm<EnquiryFormData>({
            product_id: productId,
            name: '',
            email: '',
            phone: '',
            message: '',
        });

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        post('/enquiries', {
            preserveScroll: true,
            onSuccess: () => {
                setSubmitted(true);
                reset();
            },
        });
    }

    if (submitted) {
        return (
            <div
                role="status"
                className="rounded-md border border-emerald-200 bg-emerald-50 p-4 text-emerald-800"
            >
                Thank you! Your enquiry has been sent — we'll be in touch
                shortly.
            </div>
        );
    }

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
            <div>
                <label
                    htmlFor="name"
                    className="block text-sm font-medium text-slate-700"
                >
                    Name
                </label>
                <input
                    id="name"
                    type="text"
                    required
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                />
                {errors.name && (
                    <p role="alert" className="mt-1 text-sm text-red-600">
                        {errors.name}
                    </p>
                )}
            </div>

            <div>
                <label
                    htmlFor="email"
                    className="block text-sm font-medium text-slate-700"
                >
                    Email
                </label>
                <input
                    id="email"
                    type="email"
                    required
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                />
                {errors.email && (
                    <p role="alert" className="mt-1 text-sm text-red-600">
                        {errors.email}
                    </p>
                )}
            </div>

            <div>
                <label
                    htmlFor="phone"
                    className="block text-sm font-medium text-slate-700"
                >
                    Phone <span className="text-slate-400">(optional)</span>
                </label>
                <input
                    id="phone"
                    type="tel"
                    value={data.phone}
                    onChange={(e) => setData('phone', e.target.value)}
                    className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                />
                {errors.phone && (
                    <p role="alert" className="mt-1 text-sm text-red-600">
                        {errors.phone}
                    </p>
                )}
            </div>

            <div>
                <label
                    htmlFor="message"
                    className="block text-sm font-medium text-slate-700"
                >
                    Message
                </label>
                <textarea
                    id="message"
                    required
                    rows={4}
                    value={data.message}
                    onChange={(e) => setData('message', e.target.value)}
                    className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                />
                {errors.message && (
                    <p role="alert" className="mt-1 text-sm text-red-600">
                        {errors.message}
                    </p>
                )}
            </div>

            <button
                type="submit"
                disabled={processing}
                className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {processing ? 'Sending…' : 'Submit enquiry'}
            </button>
        </form>
    );
}
