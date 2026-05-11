
import { useRef } from "react";
import { useToast } from "@/hooks/use-toast";
import html2canvas from "html2canvas";
import jsPDF from "jspdf";
import type { FunnelData } from "@/pages/FunnelCalculator";
import { formatNumber, formatCurrency } from "@/utils/formatNumbers";

interface FunnelPDFExportProps {
  data: FunnelData;
  reportRef: React.RefObject<HTMLDivElement>;
}

const FunnelPDFExport = ({ data, reportRef }: FunnelPDFExportProps) => {
  const { toast } = useToast();
  
  const exportPDF = async () => {
    if (!reportRef.current) return;
    
    try {
      toast({
        title: "Preparing PDF...",
        description: "Please wait while we generate your report."
      });
      
      const canvas = await html2canvas(reportRef.current, {
        scale: 2,
        logging: false,
        useCORS: true,
        backgroundColor: "#ffffff"
      });
      
      const imgData = canvas.toDataURL('image/png');
      const pdf = new jsPDF('p', 'mm', 'a4');
      const imgWidth = 190;
      const pageHeight = 297;
      const imgHeight = (canvas.height * imgWidth) / canvas.width;
      
      // Professional header with gradient
      pdf.setFillColor(67, 56, 202); // venture-600
      pdf.rect(0, 0, 210, 25, 'F');
      
      // Company logo area
      pdf.setFillColor(255, 255, 255);
      pdf.roundedRect(10, 5, 40, 15, 2, 2, 'F');
      
      pdf.setFont('helvetica', 'bold');
      pdf.setTextColor(67, 56, 202);
      pdf.setFontSize(14);
      pdf.text('Booming', 12, 13);
      pdf.setTextColor(126, 34, 206);
      pdf.text('Venture', 12, 18);
      
      // Report title
      pdf.setFont('helvetica', 'bold');
      pdf.setTextColor(255, 255, 255);
      pdf.setFontSize(20);
      pdf.text('Funnel Leak Analysis Report', 105, 15, { align: 'center' });
      
      // Subtitle
      pdf.setFont('helvetica', 'normal');
      pdf.setFontSize(12);
      pdf.text('Professional Marketing Performance Analysis', 105, 21, { align: 'center' });
      
      // Main content
      const yPosition = 35;
      pdf.addImage(imgData, 'PNG', 10, yPosition, imgWidth, imgHeight);
      
      // Executive Summary Section
      const summaryY = yPosition + imgHeight + 20;
      
      // Section header with background
      pdf.setFillColor(249, 250, 251);
      pdf.rect(10, summaryY - 5, 190, 10, 'F');
      
      pdf.setFont('helvetica', 'bold');
      pdf.setFontSize(16);
      pdf.setTextColor(67, 56, 202);
      pdf.text('Executive Summary', 15, summaryY);
      
      // Key metrics in styled boxes
      const metricsY = summaryY + 15;
      
      // Current Performance Box
      pdf.setFillColor(236, 253, 245); // green-50
      pdf.setDrawColor(34, 197, 94); // green-500
      pdf.roundedRect(10, metricsY, 60, 25, 2, 2, 'FD');
      
      pdf.setFont('helvetica', 'bold');
      pdf.setTextColor(22, 163, 74);
      pdf.setFontSize(10);
      pdf.text('Current Performance', 12, metricsY + 5);
      
      pdf.setFont('helvetica', 'normal');
      pdf.setTextColor(21, 128, 61);
      pdf.setFontSize(9);
      pdf.text(`Visitors: ${formatNumber(data.visitors)}`, 12, metricsY + 10);
      pdf.text(`Opt-ins: ${formatNumber(data.optIns || 0)}`, 12, metricsY + 14);
      pdf.text(`Sales: ${formatNumber(data.sales || 0)}`, 12, metricsY + 18);
      pdf.text(`Revenue: ${formatCurrency(data.revenue || 0)}`, 12, metricsY + 22);
      
      // Lost Opportunities Box
      if (data.lostRevenue) {
        pdf.setFillColor(254, 242, 242); // red-50
        pdf.setDrawColor(239, 68, 68); // red-500
        pdf.roundedRect(75, metricsY, 60, 25, 2, 2, 'FD');
        
        pdf.setFont('helvetica', 'bold');
        pdf.setTextColor(220, 38, 38);
        pdf.setFontSize(10);
        pdf.text('Lost Opportunities', 77, metricsY + 5);
        
        pdf.setFont('helvetica', 'normal');
        pdf.setTextColor(185, 28, 28);
        pdf.setFontSize(9);
        pdf.text(`Lost Leads: ${formatNumber(data.lostOptIns || 0)}`, 77, metricsY + 10);
        pdf.text(`Lost Sales: ${formatNumber(data.lostSales || 0)}`, 77, metricsY + 14);
        pdf.text(`Lost Revenue: ${formatCurrency(data.lostRevenue)}`, 77, metricsY + 18);
      }
      
      // Optimization Potential Box
      pdf.setFillColor(239, 246, 255); // blue-50
      pdf.setDrawColor(59, 130, 246); // blue-500
      pdf.roundedRect(140, metricsY, 60, 25, 2, 2, 'FD');
      
      pdf.setFont('helvetica', 'bold');
      pdf.setTextColor(37, 99, 235);
      pdf.setFontSize(10);
      pdf.text('Optimization Potential', 142, metricsY + 5);
      
      pdf.setFont('helvetica', 'normal');
      pdf.setTextColor(29, 78, 216);
      pdf.setFontSize(9);
      const potentialIncrease = ((data.lostOptIns || 0) / data.visitors * 100).toFixed(1);
      pdf.text(`+${potentialIncrease}% more leads`, 142, metricsY + 10);
      pdf.text(`Industry benchmark: 25%`, 142, metricsY + 14);
      pdf.text(`Current rate: ${((data.optIns || 0) / data.visitors * 100).toFixed(1)}%`, 142, metricsY + 18);
      
      // Recommendations Section
      const recY = metricsY + 35;
      
      pdf.setFillColor(249, 250, 251);
      pdf.rect(10, recY - 5, 190, 10, 'F');
      
      pdf.setFont('helvetica', 'bold');
      pdf.setFontSize(16);
      pdf.setTextColor(67, 56, 202);
      pdf.text('Strategic Recommendations', 15, recY);
      
      const recommendations = [
        '• Optimize landing page design and copy to reduce visitor drop-off',
        '• Implement compelling lead magnets to improve opt-in conversion rates',
        '• Set up automated email sequences to nurture leads more effectively',
        '• Deploy retargeting campaigns to re-engage lost visitors',
        '• A/B test different value propositions and call-to-action elements',
        '• Schedule a consultation for a personalized optimization strategy'
      ];
      
      pdf.setFont('helvetica', 'normal');
      pdf.setFontSize(11);
      pdf.setTextColor(75, 85, 99);
      
      recommendations.forEach((rec, i) => {
        pdf.text(rec, 15, recY + 10 + (i * 6));
      });
      
      // Professional footer
      const footerY = pageHeight - 20;
      
      pdf.setFillColor(67, 56, 202);
      pdf.rect(0, footerY - 5, 210, 20, 'F');
      
      pdf.setFont('helvetica', 'bold');
      pdf.setTextColor(255, 255, 255);
      pdf.setFontSize(12);
      pdf.text('Booming Venture', 15, footerY + 2);
      
      pdf.setFont('helvetica', 'normal');
      pdf.setFontSize(9);
      pdf.text('Rotterdam, The Netherlands', 15, footerY + 7);
      pdf.text('info@boomingventure.com', 15, footerY + 11);
      
      const now = new Date();
      pdf.text(`Generated: ${now.toLocaleDateString()} ${now.toLocaleTimeString()}`, 105, footerY + 2, { align: 'center' });
      
      pdf.text('Schedule your free consultation', 195, footerY + 7, { align: 'right' });
      pdf.text('+31 10 123 4567', 195, footerY + 11, { align: 'right' });
      
      pdf.save('funnel-leak-analysis-report.pdf');
      
      toast({
        title: "Report Downloaded",
        description: "Your professional funnel analysis has been saved as a PDF."
      });
    } catch (error) {
      console.error(error);
      toast({
        title: "Export Failed",
        description: "There was a problem generating your PDF. Please try again.",
        variant: "destructive"
      });
    }
  };

  return exportPDF;
};

export default FunnelPDFExport;
