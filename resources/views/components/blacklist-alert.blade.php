<div style="background-color: #450a0a; border: 1px solid #991b1b; border-left: 6px solid #f87171; padding: 20px; border-radius: 12px; margin-bottom: 24px; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);">
    <div style="display: flex; align-items: flex-start; gap: 16px;">
        <!-- Subtle Icon -->
        <div style="color: #f87171; margin-top: 2px;">
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <div style="flex: 1;">
            <!-- Header -->
            <h3 style="margin: 0 0 6px 0; font-size: 13px; font-weight: 700; color: #fecdd3; text-transform: uppercase; letter-spacing: 1.5px;">
                Security Alert: Blacklisted Account
            </h3>
            
            <!-- Description -->
            <p style="margin: 0 0 16px 0; font-size: 12px; color: #fda4af; line-height: 1.6; opacity: 0.9;">
                The system has identified a security match within our restricted database. This transaction has been flagged and suspended.
            </p>

            <!-- Refined Detail Box -->
            <div style="background: rgba(0, 0, 0, 0.2); padding: 14px; border-radius: 8px; border: 1px solid rgba(248, 113, 113, 0.15);">
                <table style="width: 100%; border-collapse: collapse; color: #ffffff; font-size: 12px;">
                    <tr>
                        <td style="padding: 3px 0; color: #f87171; font-weight: 600; width: 120px; text-transform: uppercase; letter-spacing: 0.5px;">Holder Name</td>
                        <td style="padding: 3px 0; font-weight: 400; opacity: 0.9;">: {{ strtoupper($blacklist->nama_rekening) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 0; color: #f87171; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Bank Entity</td>
                        <td style="padding: 3px 0; font-weight: 400; opacity: 0.9;">: {{ strtoupper($blacklist->bankname?->bank_nama ?? 'UNDEFINED') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0 3px 0; color: #f87171; font-weight: 600; vertical-align: top; text-transform: uppercase; letter-spacing: 0.5px;">Reason</td>
                        <td style="padding: 8px 0 3px 0; font-weight: 400; font-style: italic; color: #fecdd3;">
                            : {{ $blacklist->keterangan ?: 'Restricted by system security protocols' }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Minimal Footer Tag -->
            <div style="margin-top: 12px; font-size: 10px; font-weight: 500; color: #f87171; opacity: 0.7; letter-spacing: 1px; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                <span style="width: 4px; height: 4px; background: #f87171; border-radius: 50%;"></span>
                Automated security verification active
            </div>
        </div>
    </div>
</div>