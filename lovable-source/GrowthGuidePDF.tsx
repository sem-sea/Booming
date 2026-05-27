import jsPDF from 'jspdf';

export interface GrowthGuideContent {
  title: string;
  sections: {
    title: string;
    content: string[];
  }[];
}

const generateGrowthGuidePDF = (): jsPDF => {
  const pdf = new jsPDF();
  const pageWidth = pdf.internal.pageSize.getWidth();
  const pageHeight = pdf.internal.pageSize.getHeight();
  const margin = 20;
  const maxWidth = pageWidth - 2 * margin;
  
  // Professional header with brand colors
  pdf.setFillColor(59, 130, 246); // booming-600 color
  pdf.rect(0, 0, pageWidth, 40, 'F');
  
  // Title
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(24);
  pdf.setFont("helvetica", "bold");
  pdf.text("Free Growth Strategy Guide", margin, 20);
  
  // Subtitle
  pdf.setFontSize(14);
  pdf.setFont("helvetica", "normal");
  pdf.text("Learn the exact strategies our clients use to achieve 48%+ growth", margin, 28);
  
  // Footer subtitle
  pdf.setFontSize(10);
  pdf.text("Research-backed AI marketing strategies with proven ROI", margin, 35);
  
  let yPos = 60;
  
  // Reset text color for content
  pdf.setTextColor(0, 0, 0);
  
  // Table of Contents
  pdf.setFontSize(18);
  pdf.setFont("helvetica", "bold");
  pdf.text("Table of Contents", margin, yPos);
  yPos += 15;
  
  pdf.setFontSize(11);
  pdf.setFont("helvetica", "normal");
  const tocItems = [
    "1. Why AI Growth Works: Key Trends & Statistics....................3",
    "2. The Five-Stage AI-Driven Growth Blueprint......................5", 
    "3. AI Implementation Roadmap......................................7",
    "4. ROI Calculator Templates.......................................9",
    "5. Client Success Stories........................................11",
    "6. Your Growth Checklist.........................................13",
    "7. Take Action Now...............................................14"
  ];
  
  tocItems.forEach(item => {
    pdf.text(item, margin + 5, yPos);
    yPos += 7;
  });
  
  // Add new page
  pdf.addPage();
  yPos = 40;
  
  // Section 1: Why AI Growth Works
  pdf.setFillColor(59, 130, 246);
  pdf.rect(0, 20, pageWidth, 15, 'F');
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(18);
  pdf.setFont("helvetica", "bold");
  pdf.text("1. Why AI Growth Works: Key Trends & Statistics", margin, 30);
  
  pdf.setTextColor(0, 0, 0);
  pdf.setFontSize(11);
  pdf.setFont("helvetica", "normal");
  
  const statsContent = [
    "AI marketing adoption surged to 88% in 2025, with 93% using it to speed up content creation — boosting efficiency and output significantly.",
    "",
    "Companies implementing AI-driven content strategies reported an average +20% increase in marketing ROI versus conventional methods.",
    "",
    "AI-generated creatives yield 47% higher CTR and 29% lower CPA, thanks to enhanced targeting and personalization.",
    "",
    "Automation in marketing leads to 75% faster campaign launches, accelerating go-to-market timelines.",
    "",
    "Marketing automation drives 451% increase in qualified leads, with 76% of companies seeing positive ROI in under a year.",
    "",
    "Hyper-personalization adds up: AI-led marketing strategies deliver up to 8× ROI and >10% sales lift.",
    "",
    "These figures show AI not as a peripheral tool, but as a core engine for growth — when applied strategically."
  ];
  
  statsContent.forEach(line => {
    if (line === "") {
      yPos += 3;
    } else {
      const wrappedText = pdf.splitTextToSize(line, maxWidth);
      pdf.text(wrappedText, margin, yPos);
      yPos += wrappedText.length * 5 + 2;
    }
    
    if (yPos > pageHeight - 30) {
      pdf.addPage();
      yPos = 40;
    }
  });
  
  // Key Statistics Box
  yPos += 10;
  pdf.setFillColor(236, 253, 245); // green-50
  pdf.setDrawColor(34, 197, 94); // green-500
  pdf.roundedRect(margin, yPos, maxWidth, 40, 3, 3, 'FD');
  
  pdf.setFont("helvetica", "bold");
  pdf.setTextColor(22, 163, 74);
  pdf.setFontSize(12);
  pdf.text("KEY PERFORMANCE INDICATORS", margin + 5, yPos + 8);
  
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(10);
  pdf.setTextColor(21, 128, 61);
  pdf.text("• 88% AI adoption rate in marketing (2025)", margin + 5, yPos + 16);
  pdf.text("• 451% increase in qualified leads", margin + 5, yPos + 22);
  pdf.text("• 47% higher CTR with AI-generated creatives", margin + 5, yPos + 28);
  pdf.text("• 8× ROI potential with hyper-personalization", margin + 5, yPos + 34);
  
  // Add new page for Section 2
  pdf.addPage();
  yPos = 40;
  
  // Section 2: Five-Stage Blueprint
  pdf.setFillColor(67, 56, 202); // venture-600
  pdf.rect(0, 20, pageWidth, 15, 'F');
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(18);
  pdf.setFont("helvetica", "bold");
  pdf.text("2. The Five-Stage AI-Driven Growth Blueprint", margin, 30);
  
  pdf.setTextColor(0, 0, 0);
  pdf.setFontSize(11);
  pdf.setFont("helvetica", "normal");
  
  const blueprintIntro = "Our clients consistently achieve 48%+ annual growth by following this proven framework:";
  pdf.text(blueprintIntro, margin, yPos);
  yPos += 15;
  
  const stages = [
    {
      title: "1. Strategy & Positioning",
      content: [
        "• Clearly define who you serve, why it matters, and how AI adds scalable value",
        "• Segment by market: Premium Brands, B2B/SaaS, E-commerce, Corporate Transformation",
        "• Develop AI-first value propositions that resonate with your target audience",
        "• Create competitive differentiation through intelligent automation"
      ]
    },
    {
      title: "2. Funnel Audit & Funnel Leak Detector", 
      content: [
        "• Use AI-powered audits to identify where leads drop off",
        "• Prioritize quick wins in TOFU, MOFU, BOFU with measurable ROI",
        "• Implement heat mapping and user journey analytics",
        "• Deploy conversion rate optimization strategies at each funnel stage"
      ]
    },
    {
      title: "3. AI-Powered Content & Creative",
      content: [
        "• Automate creative drafts, headlines, A/B testing",
        "• Balance automation with brand voice to maintain quality", 
        "• Generate personalized content at scale across all channels",
        "• Implement dynamic creative optimization for paid campaigns"
      ]
    },
    {
      title: "4. Lead Intelligence & Personalization",
      content: [
        "• Score leads using behavioral AI models",
        "• Trigger personalized workflows across email, chat, and ads",
        "• Deploy predictive analytics for customer lifetime value",
        "• Create intelligent retargeting sequences based on user behavior"
      ]
    },
    {
      title: "5. Dashboarding & Continuous Optimization",
      content: [
        "• Build real-time ROI dashboards tracking CAC, LTV, CPL",
        "• Optimize continually based on data (CTR, conversions, churn)",
        "• Implement automated alert systems for performance anomalies",
        "• Create executive-level reporting with actionable insights"
      ]
    }
  ];
  
  stages.forEach(stage => {
    if (yPos > pageHeight - 60) {
      pdf.addPage();
      yPos = 40;
    }
    
    pdf.setFont("helvetica", "bold");
    pdf.setFontSize(13);
    pdf.setTextColor(67, 56, 202);
    pdf.text(stage.title, margin, yPos);
    yPos += 8;
    
    pdf.setFont("helvetica", "normal");
    pdf.setFontSize(10);
    pdf.setTextColor(0, 0, 0);
    
    stage.content.forEach(item => {
      const wrappedText = pdf.splitTextToSize(item, maxWidth - 10);
      pdf.text(wrappedText, margin + 5, yPos);
      yPos += wrappedText.length * 5 + 2;
    });
    yPos += 8;
  });
  
  // Add new page for Section 3
  pdf.addPage();
  yPos = 40;
  
  // Section 3: Implementation Roadmap
  pdf.setFillColor(59, 130, 246);
  pdf.rect(0, 20, pageWidth, 15, 'F');
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(18);
  pdf.setFont("helvetica", "bold");
  pdf.text("3. AI Implementation Roadmap", margin, 30);
  
  pdf.setTextColor(0, 0, 0);
  pdf.setFontSize(11);
  pdf.setFont("helvetica", "normal");
  
  // Roadmap table
  const roadmapData = [
    ["Phase", "Focus", "Quick Wins", "Timeline"],
    ["1", "Audit & Baseline", "Funnel Leak Detector, gap analysis", "Week 1-2"],
    ["2", "Content & Channel Automation", "AI for social, blog, email, ads", "Week 3-6"], 
    ["3", "Lead Intent & Scoring", "AI lead scoring, segmentation, tagging", "Week 7-10"],
    ["4", "Optimization & Retention", "Retargeting, content personalization", "Week 11-14"],
    ["5", "Real-Time Reporting", "Central dashboard, KPI tracking", "Week 15-16"]
  ];
  
  // Table header
  pdf.setFillColor(240, 240, 240);
  pdf.rect(margin, yPos, maxWidth, 8, 'F');
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(9);
  
  const colWidths = [20, 50, 80, 40];
  let xPos = margin;
  roadmapData[0].forEach((header, i) => {
    pdf.text(header, xPos + 2, yPos + 5);
    xPos += colWidths[i];
  });
  yPos += 8;
  
  // Table rows
  pdf.setFont("helvetica", "normal");
  roadmapData.slice(1).forEach(row => {
    xPos = margin;
    row.forEach((cell, i) => {
      const wrappedText = pdf.splitTextToSize(cell, colWidths[i] - 4);
      pdf.text(wrappedText, xPos + 2, yPos + 4);
      xPos += colWidths[i];
    });
    
    // Draw row border
    pdf.setDrawColor(200, 200, 200);
    pdf.rect(margin, yPos, maxWidth, 12, 'D');
    yPos += 12;
  });
  
  // Add new page for Section 4
  pdf.addPage();
  yPos = 40;
  
  // Section 4: ROI Calculator Templates
  pdf.setFillColor(67, 56, 202);
  pdf.rect(0, 20, pageWidth, 15, 'F');
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(18);
  pdf.setFont("helvetica", "bold");
  pdf.text("4. ROI Calculator Templates Included", margin, 30);
  
  pdf.setTextColor(0, 0, 0);
  pdf.setFontSize(11);
  pdf.setFont("helvetica", "normal");
  
  const roiIntro = "You'll receive customizable Excel/Notion templates to calculate:";
  pdf.text(roiIntro, margin, yPos);
  yPos += 15;
  
  const roiTemplates = [
    "• MQL → SQL conversion uplift tracking",
    "• Reduction in CAC via automation implementation", 
    "• Channel-level ROI analysis (content, ads, email)",
    "• Investment payback period calculations (setup vs ROI)",
    "• Customer lifetime value optimization metrics",
    "• Marketing attribution modeling templates"
  ];
  
  roiTemplates.forEach(template => {
    pdf.text(template, margin, yPos);
    yPos += 7;
  });
  
  yPos += 10;
  
  // ROI Formula Box
  pdf.setFillColor(239, 246, 255); // blue-50
  pdf.setDrawColor(59, 130, 246); // blue-500
  pdf.roundedRect(margin, yPos, maxWidth, 50, 3, 3, 'FD');
  
  pdf.setFont("helvetica", "bold");
  pdf.setTextColor(37, 99, 235);
  pdf.setFontSize(12);
  pdf.text("ESSENTIAL ROI FORMULAS", margin + 5, yPos + 8);
  
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(9);
  pdf.setTextColor(29, 78, 216);
  pdf.text("• Customer Acquisition Cost (CAC) = Total Marketing Spend / New Customers", margin + 5, yPos + 16);
  pdf.text("• Return on Ad Spend (ROAS) = Revenue / Ad Spend", margin + 5, yPos + 22);
  pdf.text("• Customer Lifetime Value (CLV) = AOV × Purchase Frequency × Lifespan", margin + 5, yPos + 28);
  pdf.text("• Marketing ROI = (Revenue - Marketing Cost) / Marketing Cost × 100", margin + 5, yPos + 34);
  pdf.text("• Lead Conversion Rate = Converted Leads / Total Leads × 100", margin + 5, yPos + 40);
  pdf.text("• Email Open Rate Optimization = Opens / Delivered Emails × 100", margin + 5, yPos + 46);
  
  yPos += 60;
  
  const roiNote = "These tools are ideal for securing budget approval or presenting ROI projections to stakeholders and executive teams.";
  const wrappedNote = pdf.splitTextToSize(roiNote, maxWidth);
  pdf.setTextColor(0, 0, 0);
  pdf.setFont("helvetica", "italic");
  pdf.text(wrappedNote, margin, yPos);
  
  // Add new page for Section 5
  pdf.addPage();
  yPos = 40;
  
  // Section 5: Client Success Stories
  pdf.setFillColor(59, 130, 246);
  pdf.rect(0, 20, pageWidth, 15, 'F');
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(18);
  pdf.setFont("helvetica", "bold");
  pdf.text("5. Client Success Stories", margin, 30);
  
  pdf.setTextColor(0, 0, 0);
  pdf.setFontSize(11);
  pdf.setFont("helvetica", "normal");
  
  const caseStudies = [
    {
      title: "B2B SaaS Startup",
      results: "37% lower CAC and 2× pipeline velocity",
      strategy: "AI email sequences and advanced lead scoring implementation",
      details: "Challenge: High acquisition costs and slow sales cycles\nSolution: Deployed behavioral AI models for lead scoring and automated nurture sequences\nResults: Reduced CAC from €89 to €56, doubled pipeline velocity from 45 to 23 days"
    },
    {
      title: "Premium Lifestyle Brand", 
      results: "3.5× content output with maintained brand consistency",
      strategy: "AI-assisted content creation with brand voice training",
      details: "Challenge: Content bottlenecks limiting social media growth\nSolution: Custom AI content generators trained on brand voice and guidelines\nResults: Increased content output 350%, maintained 98% brand consistency score"
    },
    {
      title: "International Fintech",
      results: "€1.2M revenue impact across 5 regions", 
      strategy: "Unified AI dashboards and cross-regional optimization",
      details: "Challenge: Fragmented reporting and inconsistent performance across markets\nSolution: Centralized AI analytics platform with regional customization\nResults: 23% average performance improvement, €1.2M additional revenue"
    }
  ];
  
  caseStudies.forEach((study, index) => {
    if (yPos > pageHeight - 80) {
      pdf.addPage();
      yPos = 40;
    }
    
    // Case study box
    pdf.setFillColor(248, 250, 252); // gray-50
    pdf.setDrawColor(203, 213, 225); // gray-300
    pdf.roundedRect(margin, yPos, maxWidth, 65, 2, 2, 'FD');
    
    pdf.setFont("helvetica", "bold");
    pdf.setFontSize(12);
    pdf.setTextColor(67, 56, 202);
    pdf.text(study.title, margin + 5, yPos + 8);
    
    pdf.setFont("helvetica", "bold");
    pdf.setFontSize(10);
    pdf.setTextColor(22, 163, 74); // green
    pdf.text(study.results, margin + 5, yPos + 16);
    
    pdf.setFont("helvetica", "normal");
    pdf.setFontSize(9);
    pdf.setTextColor(75, 85, 99);
    pdf.text(`Strategy: ${study.strategy}`, margin + 5, yPos + 24);
    
    const wrappedDetails = pdf.splitTextToSize(study.details, maxWidth - 10);
    pdf.text(wrappedDetails, margin + 5, yPos + 32);
    
    yPos += 75;
  });
  
  // Add new page for Section 6
  pdf.addPage();
  yPos = 40;
  
  // Section 6: Growth Checklist
  pdf.setFillColor(67, 56, 202);
  pdf.rect(0, 20, pageWidth, 15, 'F');
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(18);
  pdf.setFont("helvetica", "bold");
  pdf.text("6. Your Growth Checklist", margin, 30);
  
  pdf.setTextColor(0, 0, 0);
  pdf.setFontSize(11);
  pdf.setFont("helvetica", "normal");
  
  const checklistItems = [
    "✓ Audit your funnel with AI-powered tracking tools",
    "✓ Identify quick wins in email marketing, SEO, and paid ads",
    "✓ Deploy AI for content creation, creative optimization, and lead scoring", 
    "✓ Launch a 14-day growth sprint with measurable KPIs",
    "✓ Use our ROI templates for validation and scaling decisions",
    "✓ Implement behavioral analytics and user journey mapping",
    "✓ Set up automated lead nurturing workflows",
    "✓ Create personalized customer experiences at scale",
    "✓ Establish real-time performance monitoring dashboards",
    "✓ Schedule regular optimization reviews and strategy adjustments"
  ];
  
  checklistItems.forEach(item => {
    pdf.text(item, margin, yPos);
    yPos += 8;
  });
  
  yPos += 15;
  
  // Implementation Timeline
  pdf.setFillColor(254, 242, 242); // red-50
  pdf.setDrawColor(239, 68, 68); // red-500
  pdf.roundedRect(margin, yPos, maxWidth, 30, 3, 3, 'FD');
  
  pdf.setFont("helvetica", "bold");
  pdf.setTextColor(220, 38, 38);
  pdf.setFontSize(12);
  pdf.text("RECOMMENDED IMPLEMENTATION TIMELINE", margin + 5, yPos + 8);
  
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(10);
  pdf.setTextColor(185, 28, 28);
  pdf.text("Week 1-2: Complete funnel audit and baseline metrics", margin + 5, yPos + 16);
  pdf.text("Week 3-4: Deploy first AI automation and begin testing", margin + 5, yPos + 22);
  pdf.text("Week 5-8: Scale successful implementations and optimize", margin + 5, yPos + 26);
  
  // Add final page for Section 7
  pdf.addPage();
  yPos = 40;
  
  // Section 7: Take Action Now
  pdf.setFillColor(59, 130, 246);
  pdf.rect(0, 20, pageWidth, 15, 'F');
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(18);
  pdf.setFont("helvetica", "bold");
  pdf.text("7. Take Action Now", margin, 30);
  
  pdf.setTextColor(0, 0, 0);
  pdf.setFontSize(11);
  pdf.setFont("helvetica", "normal");
  
  const actionIntro = "This guide isn't fluffy theory — it's a done-for-you blueprint used by our clients. We guarantee:";
  pdf.text(actionIntro, margin, yPos);
  yPos += 15;
  
  const guarantees = [
    "• Repeatable 48%+ year-over-year growth methodology",
    "• Automated funnels that scale without additional headcount",
    "• Immediate ROI visible from your first growth sprint",
    "• Proven frameworks tested across 15+ businesses",
    "• Complete implementation roadmap with timelines"
  ];
  
  guarantees.forEach(guarantee => {
    pdf.text(guarantee, margin, yPos);
    yPos += 8;
  });
  
  yPos += 15;
  
  // Call to Action Box
  pdf.setFillColor(67, 56, 202);
  pdf.roundedRect(margin, yPos, maxWidth, 40, 5, 5, 'F');
  
  pdf.setTextColor(255, 255, 255);
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(14);
  pdf.text("Ready to implement these strategies?", margin + 10, yPos + 12);
  
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(11);
  pdf.text("Schedule a free strategy session to discuss your specific growth goals", margin + 10, yPos + 22);
  pdf.text("and discover how AI can accelerate your business growth.", margin + 10, yPos + 30);
  
  yPos += 50;
  
  // Sources section
  pdf.setFont("helvetica", "bold");
  pdf.setTextColor(0, 0, 0);
  pdf.setFontSize(12);
  pdf.text("Sources & Research", margin, yPos);
  yPos += 10;
  
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(8);
  pdf.setTextColor(100, 100, 100);
  const sources = [
    "• AI usage and ROI trends — AllAboutAI, AInvest, WinSavvy, McKinsey & Company",
    "• Marketing automation statistics — ProfileTree, RevvGrowth, Iterable",
    "• ROI multipliers and performance data — McKinsey, Deloitte, PwC",
    "• Conversion rate benchmarks — HubSpot, Salesforce, Adobe Analytics",
    "• Lead generation statistics — Demand Gen Report, Marketing Sherpa"
  ];
  
  sources.forEach(source => {
    pdf.text(source, margin, yPos);
    yPos += 6;
  });
  
  // Professional footer on last page
  const footerY = pageHeight - 30;
  
  pdf.setFillColor(67, 56, 202);
  pdf.rect(0, footerY, 210, 30, 'F');
  
  pdf.setFont('helvetica', 'bold');
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(14);
  pdf.text('Booming Venture', 15, footerY + 8);
  
  pdf.setFont('helvetica', 'normal');
  pdf.setFontSize(10);
  pdf.text('AI-Powered Marketing Solutions', 15, footerY + 15);
  pdf.text('Rotterdam, The Netherlands', 15, footerY + 21);
  pdf.text('info@boomingventure.com', 15, footerY + 27);
  
  const now = new Date();
  pdf.text(`Generated: ${now.toLocaleDateString()}`, 105, footerY + 8, { align: 'center' });
  pdf.text('Premium Growth Strategy Guide', 105, footerY + 15, { align: 'center' });
  
  pdf.text('Ready to grow?', 195, footerY + 8, { align: 'right' });
  pdf.text('Schedule your consultation', 195, footerY + 15, { align: 'right' });
  pdf.text('+31 10 123 4567', 195, footerY + 21, { align: 'right' });
  
  return pdf;
};

export const downloadGrowthGuidePDF = () => {
  // Open the Google Drive PDF in a new tab for download
  window.open('https://drive.google.com/file/d/1b7gSJEjYJYw6LPD-IAAjVlSTAKqx24XS/view?usp=sharing', '_blank');
};

export default generateGrowthGuidePDF;
