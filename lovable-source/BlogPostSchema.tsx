
interface BlogPostSchemaProps {
  title: string;
  description: string;
  url: string;
  publishDate: string;
  modifiedDate?: string;
  readTime: string;
  image?: string;
  category?: string;
}

const BlogPostSchema = ({ 
  title, 
  description, 
  url, 
  publishDate, 
  modifiedDate, 
  readTime, 
  image,
  category 
}: BlogPostSchemaProps) => {
  const blogPostSchema = {
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": title,
    "description": description,
    "url": url,
    "datePublished": publishDate,
    "dateModified": modifiedDate || publishDate,
    "author": {
      "@type": "Organization",
      "name": "Booming Venture",
      "url": "https://boomingventure.com"
    },
    "publisher": {
      "@type": "Organization",
      "name": "Booming Venture",
      "logo": {
        "@type": "ImageObject",
        "url": "https://boomingventure.com/lovable-uploads/21172c30-65ed-42bf-8a4b-b92cbd2b246e.png"
      }
    },
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": url
    },
    "image": image || "https://boomingventure.com/lovable-uploads/4e357139-5a7e-4336-8796-94013f33dc3d.png",
    "articleSection": category || "Marketing",
    "keywords": ["marketing", "AI", "business growth", "Netherlands", "Rotterdam"],
    "timeRequired": readTime,
    "inLanguage": "en-US"
  };

  const breadcrumbSchema = {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "Home",
        "item": "https://boomingventure.com"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "Blog",
        "item": "https://boomingventure.com/blog"
      },
      {
        "@type": "ListItem",
        "position": 3,
        "name": title,
        "item": url
      }
    ]
  };

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(blogPostSchema) }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbSchema) }}
      />
    </>
  );
};

export default BlogPostSchema;
