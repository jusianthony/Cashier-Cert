import React, { useEffect, useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { format } from 'date-fns';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'GSIS LOAN Report', href: '/GSSloan' },
];

export default function GSSloan() {
  // 🔹 States
  const [names, setNames] = useState<string[]>([]);
  const [selectedName, setSelectedName] = useState<string>('');
  const [selectedLoans, setSelectedLoans] = useState<string[]>([]); // ✅ NEW state for checkboxes
  const [pdfUrl, setPdfUrl] = useState<string>('');
  const [startMonth, setStartMonth] = useState<string>(''); 
  const [endMonth, setEndMonth] = useState<string>('');     

  // 🔹 Loan Types
  const loanTypes = [
    'Conso Loan',
    'Emergency Loan',
    'PL Regular',
    'MPL',
    'MPL Lite'
  ];

  // 🔹 Fetch distinct employee names
  useEffect(() => {
    axios
      .get('/api/gss-loan/full-names') // ✅ Ensure this endpoint exists
      .then((res) => setNames(res.data))
      .catch((err) => console.error('Error fetching GSIS LOAN names:', err));
  }, []);

  // 🔹 Update PDF URL whenever filters change
  useEffect(() => {
    if (selectedName) {
      const params = new URLSearchParams();
      if (startMonth) params.append('start', startMonth);
      if (endMonth) params.append('end', endMonth);
      if (selectedLoans.length > 0) {
        params.append('loans', selectedLoans.join(',')); // ✅ add selected loan types
      }

      setPdfUrl(`/pdf-gss-loan-template/${encodeURIComponent(selectedName)}?${params.toString()}`);
    } else {
      setPdfUrl('');
    }
  }, [selectedName, startMonth, endMonth, selectedLoans]);

  // 🔹 Format YYYY-MM to "Month Year"
  const formatMonthYear = (value: string) => {
    if (!value) return '';
    const [year, month] = value.split('-');
    const date = new Date(Number(year), Number(month) - 1);
    return format(date, 'MMMM yyyy');
  };

  // 🔹 Handle checkbox toggle
  const handleLoanChange = (loan: string) => {
    setSelectedLoans((prev) =>
      prev.includes(loan) ? prev.filter((l) => l !== loan) : [...prev, loan]
    );
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="GSIS LOAN Report" />

      <div className="p-4 space-y-6">
        
        {/* 🔹 Filters Section */}
        <div className="flex flex-wrap gap-6 items-center">
          {/* Start Calendar */}
          <div className="flex flex-col">
            <label className="font-semibold">Start Calendar:</label>
            <input
              type="month"
              className="border rounded px-3 py-2"
              onChange={(e) => setStartMonth(formatMonthYear(e.target.value))}
            />
            {startMonth && <span className="italic text-sm text-gray-600">{startMonth}</span>}
          </div>

          {/* End Calendar */}
          <div className="flex flex-col">
            <label className="font-semibold">End Calendar:</label>
            <input
              type="month"
              className="border rounded px-3 py-2"
              onChange={(e) => setEndMonth(formatMonthYear(e.target.value))}
            />
            {endMonth && <span className="italic text-sm text-gray-600">{endMonth}</span>}
          </div>

          {/* Employee Dropdown */}
          <div className="flex flex-col">
            <label className="font-semibold">Select Employee Name:</label>
            <select
              className="border rounded px-3 py-2 w-64"
              value={selectedName}
              onChange={(e) => setSelectedName(e.target.value)}
            >
              <option value="">-- Select --</option>
              {names.map((name, idx) => (
                <option key={idx} value={name}>{name}</option>
              ))}
            </select>
          </div>
        </div>

        {/* 🔹 Loan Type Checkboxes */}
        <div className="space-y-2">
          <label className="font-semibold">Select Loan Type(s):</label>
          <div className="flex flex-wrap gap-6">
            {loanTypes.map((loan) => (
              <label key={loan} className="flex items-center gap-2">
                <input
                  type="checkbox"
                  checked={selectedLoans.includes(loan)}
                  onChange={() => handleLoanChange(loan)}
                />
                {loan}
              </label>
            ))}
          </div>
        </div>

        {/* 🔹 PDF Preview */}
        {pdfUrl ? (
          <div className="border rounded-lg overflow-hidden" style={{ height: '80vh' }}>
            <iframe
              key={pdfUrl}
              src={pdfUrl}
              width="100%"
              height="100%"
              style={{ border: 'none' }}
              title="GSIS Loan Report PDF"
            />
          </div>
        ) : (
          <p className="text-gray-500 italic">
            Select an employee and loan type(s) to view GSIS Loan report.
          </p>
        )}
      </div>
    </AppLayout>
  );
}
