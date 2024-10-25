import { FC, ReactNode, Suspense, useEffect, useState } from "react";
import { useConnectionQuality } from "@/components/ConnectionQualityProvider";

interface DelayedSuspenseProps {
  fallback: string,
  children: ReactNode
}

export const DelayedSuspense: FC<DelayedSuspenseProps> =
  ({ fallback, children }) => {
    const { isSlow } = useConnectionQuality();
    return (
      <Suspense fallback={isSlow ? fallback : <DelayedStatus message={fallback} />}>
        {children}
      </Suspense>
    );
  }

const DelayedStatus: FC<{ message: string }> = ({ message }) => {
  const { isSlow, setIsSlow, quickLoadExpiration } = useConnectionQuality();
  const [visible, setVisible] = useState(isSlow);
  useEffect(() => {
    if (visible) return;
    let timeoutId: any = setTimeout(() => {
      setVisible(true);
      setIsSlow(true);
      timeoutId = undefined;
    }, Math.max(0, quickLoadExpiration.getTime() - Date.now()));
    return () => {
      if (timeoutId) clearTimeout(timeoutId);
    }
  }, []);

  return visible ? message : null;
}
