import { useEffect, useState } from "react";
import { MediaTypes, useListQuery } from "@/features/media/mediaApi";
import './index.scss';

const Home = () => {

  const [index, setIndex] = useState(0);
  const {
    data,
    isLoading
  } = useListQuery({ offset: 0, limit: 500 });

  useEffect(() => {
    if (isLoading) return;
    setIndex(0);
  }, [isLoading]);

  useEffect(() => {
    if (!data || data.rows.length === 0) return;
    const row = data.rows[index];
    const timeout = setTimeout(() => {
      const rowCount = data?.rows.length ?? 0;
      if (rowCount === 0) return;
      setIndex((index + 1) % rowCount);
    }, row.duration ?? 5000);
    return () => {
      clearTimeout(timeout);
    }
  })

  const row = data?.rows[index];

  if (isLoading) return <div>Loading</div>;
  if (!row) return <div>No data</div>;

  if (row.mediaType === MediaTypes.video) {

    return <div style={{
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      height: '100vh',
      width: '100vw',
      overflow: 'hidden'
    }}
    >
      <video src={row.url} autoPlay muted loop></video>
    </div>
  }

  if (row.mediaType === MediaTypes.audio) {

    return <div style={{
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      height: '100vh',
      width: '100vw',
      overflow: 'hidden'
    }}
    >
      <img src={row.thumbnailUrl} style={{
        maxWidth: '100%',
        maxHeight: '100%',
        objectFit: 'contain'
      }} />
      <audio src={row.url} autoPlay></audio>
    </div>
  }

  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      height: '100vh',
      width: '100vw',
      overflow: 'hidden'
    }}
    >
      <img src={row.url} style={{
        maxWidth: '100%',
        maxHeight: '100%',
        objectFit: 'contain'
      }} />
    </div>
  );
};

export default Home;