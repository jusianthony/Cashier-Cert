import React, { useEffect, useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { format } from 'date-fns';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/GSISreport' },
];

export default function GSISReport() {
  const [names, setNames] = useState<string[]>([]);
  const [selectedName, setSelectedName] = useState<string>('');
  const [pdfUrl, setPdfUrl] = useState<string>('');
  const [startMonth, setStartMonth] = useState<string>(''); // "January 2025"
  const [endMonth, setEndMonth] = useState<string>('');     // "December 2025"

  useEffect(() => {
    axios
      .get('/api/gsis/full-names')
      .then((res) => setNames(res.data))
      .catch((err) => console.error('Error fetching names:', err));
  }, []);

  // update PDF url when filters change
  useEffect(() => {
    if (selectedName) {
      const params = new URLSearchParams();
      if (startMonth) params.append('start', startMonth);
      if (endMonth) params.append('end', endMonth);

      setPdfUrl(`/pdf-template/${encodeURIComponent(selectedName)}?${params.toString()}`);
    } else {
      setPdfUrl('');
    }
  }, [selectedName, startMonth, endMonth]);

  const formatMonthYear = (value: string) => {
    if (!value) return '';
    const [year, month] = value.split('-');
    const date = new Date(Number(year), Number(month) - 1);
    return format(date, 'MMMM yyyy'); // "January 2025"
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="GSIS Report" />

      <div className="p-4 space-y-4">
        <div className="flex items-center gap-4 flex-wrap">
          {/* Start Calendar */}
          <label className="font-semibold whitespace-nowrap">Start Calendar:</label>
          <input
            type="month"
            className="border rounded px-3 py-2"
            onChange={(e) => setStartMonth(formatMonthYear(e.target.value))}
          />
          {startMonth && <span className="italic text-sm text-gray-600">{startMonth}</span>}

          {/* End Calendar */}
          <label className="font-semibold whitespace-nowrap">End Calendar:</label>
          <input
            type="month"
            className="border rounded px-3 py-2"
            onChange={(e) => setEndMonth(formatMonthYear(e.target.value))}
          />
          {endMonth && <span className="italic text-sm text-gray-600">{endMonth}</span>}

          {/* Employee Dropdown */}
          <label className="font-semibold whitespace-nowrap">Select Employee Name:</label>
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

        {/* PDF Preview */}
        {pdfUrl ? (
          <div className="border rounded-lg overflow-hidden" style={{ height: '80vh' }}>
            <iframe
              key={pdfUrl}
              src={pdfUrl}
              width="100%"
              height="100%"
              style={{ border: 'none' }}
              title="GSIS Report PDF"
            />
          </div>
        ) : (
          <p className="text-gray-500 italic">Select an employee to view report.</p>
        )}
      </div>
    </AppLayout>
  );
}
