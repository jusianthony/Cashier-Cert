import React from 'react';

export default function Report({ person, remitted }) {
    const handlePrint = () => {
        window.print();
    };

    return (
        <div className="max-w-4xl mx-auto p-10 bg-white text-black">
            {/* HEADER */}
            <div className="flex justify-between items-center">
                <img src="/images/doh_logo.png" alt="DOH Logo" className="h-20" />
                <div className="text-center">
                    <h2 className="font-bold">Republic of the Philippines</h2>
                    <h3 className="font-semibold">DEPARTMENT OF HEALTH</h3>
                    <p>Central Luzon Center for Health Development</p>
                </div>
                <img src="/images/bagong_pilipinas.png" alt="Bagong Pilipinas Logo" className="h-20" />
            </div>

            <p className="text-right mt-4">February 19, 2025</p>

            {/* TITLE */}
            <h1 className="text-center font-bold text-lg mt-6 tracking-widest">CERTIFICATION</h1>

            {/* BODY */}
            <p className="mt-6 text-justify">
                This is to certify that as per records of this Office, the following GSIS Contributions
                of <strong>{person.first_name} {person.middle_name} {person.last_name}</strong>, DOH-NDP, 
                with BP No. <strong>{person.pagibig_acctno}</strong>, were deducted from their salary and 
                were acknowledged by Land Bank of the Philippines and Government Service Insurance System, 
                City of San Fernando, Pampanga Branch, to wit:
            </p>

            {/* TABLE */}
            <table className="w-full border-collapse border mt-6 text-sm">
                <thead>
                    <tr>
                        <th className="border px-2 py-1">Period Covered</th>
                        <th className="border px-2 py-1">PS</th>
                        <th className="border px-2 py-1">GS</th>
                        <th className="border px-2 py-1">EC</th>
                        <th className="border px-2 py-1">OR Number</th>
                        <th className="border px-2 py-1">Date Paid</th>
                    </tr>
                </thead>
                <tbody>
                    {remitted.map((row, index) => (
                        <tr key={index}>
                            <td className="border px-2 py-1">{row.date}</td>
                            <td className="border px-2 py-1">{row.employee_contribution}</td>
                            <td className="border px-2 py-1">{row.employer_contribution}</td>
                            <td className="border px-2 py-1">100.00</td>
                            <td className="border px-2 py-1">{row.orno}</td>
                            <td className="border px-2 py-1">{row.date}</td>
                        </tr>
                    ))}
                </tbody>
            </table>

            {/* FOOTER */}
            <p className="mt-6">This certification is being issued upon the request of the member.</p>
            <p className="mt-10">Certified Correct:</p>
            <p className="mt-6 font-bold underline">ELIZABETH R. FERNANDEZ</p>
            <p>Administrative Office V</p>

            {/* PRINT BUTTON (hidden in print) */}
            <div className="mt-8 no-print">
                <button 
                    onClick={handlePrint} 
                    className="px-4 py-2 bg-blue-500 text-white rounded"
                >
                    Print
                </button>
            </div>

            {/* PRINT CSS */}
            <style>
                {`
                    @media print {
                        .no-print { display: none; }
                        body { background: white; }
                    }
                `}
            </style>
        </div>
    );
}
